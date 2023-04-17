<?php

namespace SfingeBundle\Entity;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use RichiesteBundle\Form\Entity\RicercaBandoManifestazione;
use Doctrine\ORM\Query\Expr;

class BandoRepository extends EntityRepository
{
    public function cercaBandiQueryBuilder(RicercaBandoManifestazione $dati): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b');
        $expr = $qb->expr();

        $qb->leftJoin('b.atto', 'a')
        ->leftJoin('b.responsabile', 'u')
        ->leftJoin('b.asse', 'ax')
        ->leftJoin('b.amministrazione_emittente', 'am')
        ->leftJoin('b.stato_procedura', 'sp')
        ->where(
            $expr->in('sp.codice', ['IN_CORSO', 'CONCLUSO']),
            'b not instance of SfingeBundle:ProceduraPA',
            $expr->like('b.titolo', ':titolo_procedura'),
            $expr->like('a.numero', ':numero_atto'),
            $expr->eq('ax.id', 'coalesce(:asse_id, ax.id)'),
            $expr->eq('sp.codice', 'coalesce(:stato_procedura, sp.codice)'),
            $this->verificaStato($expr),
            $this->verificaTipoProcedura($expr)
        );
        // TODO: aggiungere alla query lo stato della Procedura(bandi creati, ma non pubblicati non devono essere visualizzabili: possiamo utilizzare la data pubblicazione BUR?)

        $qb->setParameter(":titolo_procedura", "%" . $dati->getTitolo() . "%");
        $qb->setParameter(":numero_atto", "%" . $dati->getAtto() . "%");
        $qb->setParameter(":asse_id", \is_null($dati->getAsse()) ? null : $dati->getAsse()->getId());
        $qb->setParameter(":stato_procedura", $dati->getStatoProcedura());
        $qb->setParameter(":stato", $dati->getStato());
        $qb->setParameter(":tipo", $dati->getTipo());

        return $qb;
    }

    private function verificaStato(Expr $expr)
    {
        return 
            $expr->orX(
                $expr->isNull(':stato'),
                $expr->andX(
                    $expr->eq(':stato', $expr->literal('APERTO')),
                    $expr->between('CURRENT_TIMESTAMP()', 'b.data_ora_inizio_presentazione', 'b.data_ora_fine_creazione')
                ),
                $expr->andX(
                    $expr->eq(':stato', $expr->literal('CHIUSO')),
                    $expr->not(
                        $expr->between('CURRENT_TIMESTAMP()', 'b.data_ora_inizio_presentazione', 'b.data_ora_fine_creazione')
                    )
                )
        );
    }

    private function verificaTipoProcedura(Expr $expr)
    {
        return $expr->orX(
            $expr->andX(
                $expr->isNull(':tipo'),
                $expr->orX(
                    'b INSTANCE OF SfingeBundle:ManifestazioneInteresse',
                    'b INSTANCE OF SfingeBundle:Bando'
                    )
            ),
            $expr->andX(
                $expr->eq(':tipo', $expr->literal('BANDO')),
                'b INSTANCE OF SfingeBundle:Bando'
            ),
            $expr->andX(
                $expr->eq(':tipo', $expr->literal('MANIFESTAZIONE_INTERESSE')),
                'b INSTANCE OF SfingeBundle:ManifestazioneInteresse'
            )
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getBandiConContatore()
    {
        $oggi = new \DateTime();
        $oggi->setTime(23,59,59);
        
        $qb = $this->createQueryBuilder('bando')
            ->where('bando.mostra_contatore_richieste_presentate = :flag_mostra_contatori')
            ->andWhere('bando.data_ora_inizio_presentazione <= :oggi')
            ->setParameter('flag_mostra_contatori', 1)
            ->setParameter('oggi', $oggi);
        return $qb->getQuery()->getResult();
    }

    /**
     * @return array|float|int|string
     */
    public function getElencoProcedureInScadenza()
    {
        $oggi = new DateTime();
        $qb = $this->createQueryBuilder('bando')
            ->where('bando.data_ora_fine_presentazione >= :oggi')
            ->andWhere('bando INSTANCE OF SfingeBundle:Bando OR bando INSTANCE OF SfingeBundle:ManifestazioneInteresse OR bando INSTANCE OF SfingeBundle:ProceduraPA')
            ->setParameter('oggi', $oggi);
        return $qb->getQuery()->getResult();
    }
}
