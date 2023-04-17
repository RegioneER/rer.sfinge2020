<?php

namespace AttuazioneControlloBundle\Repository;

use AttuazioneControlloBundle\Entity\StatoProroga;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\Proroga;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SfingeBundle\Entity\Procedura;

class ProrogheRepository extends EntityRepository {

    /**
     * @return Collection|Proroga[]
     */
    public function getUltimeProroghe(Richiesta $richiesta) {
        $tot = new ArrayCollection();
        $dql = 'select proroga '
                . 'from AttuazioneControlloBundle:Proroga proroga '
                . 'join proroga.attuazione_controllo_richiesta atc '
                . 'join atc.richiesta richiesta '
                . 'where richiesta = :richiesta '
                . 'and proroga.tipo_proroga = :avvio '
                . 'and proroga.approvata = :approvata '
                . 'order by proroga.data_avvio_approvata desc ';

        $res = $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('richiesta', $richiesta)
                ->setParameter('avvio', Proroga::PROROGA_AVVIO)
                ->setParameter('approvata', true)
                ->setMaxResults(1)
                ->getOneOrNullResult();
        if (!\is_null($res)) {
            $tot->add($res);
        }

        $dql = 'select proroga '
                . 'from AttuazioneControlloBundle:Proroga proroga '
                . 'join proroga.attuazione_controllo_richiesta atc '
                . 'join atc.richiesta richiesta '
                . 'where richiesta = :richiesta '
                . 'and proroga.tipo_proroga = :fine '
                . 'and proroga.approvata = :approvata '
                . 'order by proroga.data_fine_approvata desc ';

        $res = $this->getEntityManager()
                ->createQuery($dql)
                ->setParameter('richiesta', $richiesta)
                ->setParameter('fine', Proroga::PROROGA_FINE)
                ->setParameter('approvata', true)
                ->setMaxResults(1)
                ->getOneOrNullResult();
        if (!\is_null($res)) {
            $tot->add($res);
        }

        return $tot;
    }

    /**
     * @param $id_richiesta
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getUltimaProrogaProtocollata($id_richiesta) {
        $qb = $this->createQueryBuilder('proroga')
                ->join('proroga.stato', 'stato')
                ->join('proroga.attuazione_controllo_richiesta', 'attuazione')
                ->join('attuazione.richiesta', 'richiesta')
                ->where(
                        'richiesta.id = :id_richiesta',
                        'stato.codice = :statoProroga')
                ->orderBy('proroga.data_invio', 'DESC')
                ->setParameter('id_richiesta', $id_richiesta)
                ->setParameter('statoProroga', StatoProroga::PROROGA_PROTOCOLLATA)
                ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Procedura $procedura
     * @return array
     */
    public function iterateProroghe(Procedura $procedura) {
        $dql = 'select proroga, atc, richiesta, proroga_protocollo, proponenti, soggetto_version, soggetto, richiesta_protocollo '
                . 'from AttuazioneControlloBundle:Proroga proroga '
                . 'join proroga.attuazione_controllo_richiesta atc '
                . 'join atc.richiesta richiesta '
                . 'join richiesta.procedura procedura '
                . 'left join proroga.richieste_protocollo proroga_protocollo '
                . 'join richiesta.richieste_protocollo richiesta_protocollo ' //OneTOMany
                . 'join richiesta.proponenti proponenti ' //OneTOMany
                . 'left join proponenti.soggetto_version soggetto_version '
                . 'join proponenti.soggetto soggetto '
                . 'join proroga.stato st '
                . "where procedura = :procedura and st.codice = 'PROROGA_PROTOCOLLATA' "
        ;

        return $this
                        ->getEntityManager()
                        ->createQuery($dql)
                        ->setParameter('procedura', $procedura)
                        ->getResult();
    }

}
