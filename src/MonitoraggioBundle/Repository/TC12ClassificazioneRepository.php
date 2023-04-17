<?php

namespace MonitoraggioBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MonitoraggioBundle\Entity\TC11TipoClassificazione;
use Doctrine\ORM\QueryBuilder;
use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Entity\TC12Classificazione;
use RichiesteBundle\Entity\Richiesta;

class TC12ClassificazioneRepository extends EntityRepository {
    public function queryTipoClassificazione(TC11TipoClassificazione $tipoClassificazione, Richiesta $richiesta): QueryBuilder {
        return $this->createQueryBuilder('tc12')
                ->select('distinct tc12')
                ->join('tc12.tipo_classificazione', 'tc11')
                ->leftJoin('tc12.richieste_classificazioni', 'richieste_classificazioni')
                ->leftJoin('richieste_classificazioni.richiesta_programma', 'richiesta_programma')
                ->leftJoin('richiesta_programma.richiesta', 'richiesta', 'with', 'richiesta = :richiesta')
                ->where('tc11 = :tipoClassificazione')
                ->andWhere('richiesta.id is null')
                ->setParameter('tipoClassificazione', $tipoClassificazione)
                ->setParameter('richiesta', $richiesta);
    }

    public function ajaxRequest($q) {
        return $this->createQueryBuilder('tc12')
                ->select([
                    'id' => 'tc12.id',
                    'value' => 'tc12.descrizione value',
                    ])
                ->join('tc12.tipo_classificazione', 'tipo_classificazione')
                ->leftJoin('tc12.programma', 'programma')
                ->where('tipo_classificazione = :tipo_classificazione')
                ->andWhere('coalesce(programma, :programma) = coalesce(:programma, programma)')
                ->setParameter('tipo_classificazione', $q["TC11TipoClassificazione"])
                ->setParameter('programma', $q['TC4Programma']);
    }

    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC12 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC12Classificazione e '
                . 'join e.tipo_classificazione tipo_classificazione '
                . 'left join e.programma programma with programma = coalesce(:programma, programma)'
                . "where tipo_classificazione = coalesce(:tipo_classificazione, tipo_classificazione) ";

        if (!is_null($ricerca->getProgramma())) {
            $query .= "and programma = :programma ";
        }

        $query .= "and coalesce(e.origine_dato, '') like :origine_dato "
                . "and coalesce(e.codice, '') like :codice "
                . "and coalesce(e.descrizione, '') like :descrizione "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':tipo_classificazione', $ricerca->getTipoClassificazione());
        $q->setParameter(':programma', $ricerca->getProgramma());
        $q->setParameter(':origine_dato', '%' . $ricerca->getOrigineDato() . '%');
        $q->setParameter(':codice', '%' . $ricerca->getCodice() . '%');
        $q->setParameter(':descrizione', '%' . $ricerca->getDescrizione() . '%');

        return $q;
    }

    public function querySearchValidClassification(): QueryBuilder {
        $qb = $this->createQueryBuilder('classificazione');
        $expr = $qb->expr();
        $qb
        ->join('classificazione.tipo_classificazione', 'tipo')
        ->leftJoin('classificazione.programma', 'programma')

        ->from('AttuazioneControlloBundle:RichiestaProgramma', 'richiesta_programma')
        ->join('richiesta_programma.richiesta', 'richiesta')
        ->join('richiesta_programma.tc4_programma', 'programmi_richiesta')
        ->join('richiesta.procedura', 'procedura')
        ->join('procedura.azioni', 'azioni')
        ->join('azioni.obiettivo_specifico', 'obiettivo')
        ->leftJoin('azioni.classificazioni','classificazioni_azione')
        ->where($expr->andX(
            $expr->eq('programmi_richiesta', 'COALESCE(programma, programmi_richiesta)'),
            $expr->orX(
                $expr->eq('classificazioni_azione', 'classificazione'),
                $expr->eq('obiettivo.classificazione', 'classificazione')
            )
        ));
        return $qb;
    }

    public function findByProgrammaCodiceTipo(TC4Programma $programma, string $codice, TC11TipoClassificazione $tipo): ?TC12Classificazione {
        
        $query = "SELECT e 
                from MonitoraggioBundle:TC12Classificazione e
                join e.tipo_classificazione tipo_classificazione
                left join e.programma programma
                where
                tipo_classificazione =:tipo_classificazione
                and coalesce(programma, :programma) = :programma
                and e.codice = :codice 
        ";
        $q = $this->getEntityManager()->createQuery($query);
        $q->setParameter(':tipo_classificazione', $tipo);
        $q->setParameter(':programma', $programma);
        $q->setParameter(':codice', $codice);

        return $q->getOneOrNullResult();
    }
}
