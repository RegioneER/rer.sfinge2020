<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\StatoRichiesta;
use Doctrine\ORM\EntityRepository;
use SfingeBundle\Entity\Procedura;


class SedeOperativaRichiestaRepository extends EntityRepository
{
    /**
     * @param Procedura $procedura
     * @param null $finestra_temporale
     * @return array|int|string
     */
    public function getSediOperativeRichiestaPrenotate(Procedura $procedura, $finestra_temporale = null)
    {
        $qb = $this->createQueryBuilder('sede')
            ->join('sede.richiesta', 'richiesta')
            ->join('richiesta.stato', 'stato')
            ->join('richiesta.proponenti', 'mandatario', 'with', 'mandatario.mandatario = 1')
            ->join('mandatario.soggetto', 'soggetto')
            ->where(
                'richiesta.procedura = :procedura',
                'stato.codice in (:stati)'
            );

        if ($finestra_temporale) {
            $qb->andWhere('richiesta.finestra_temporale = :finestra_temporale');
        }
        
        $qb->setParameter('procedura', $procedura);
        $qb->setParameter('stati',[
            StatoRichiesta::PRE_INSERITA,
            StatoRichiesta::PRE_VALIDATA,
            StatoRichiesta::PRE_FIRMATA,
            StatoRichiesta::PRE_INVIATA_PA,
            StatoRichiesta::PRE_PROTOCOLLATA,
        ]);

        if ($finestra_temporale) {
            $qb->setParameter('finestra_temporale', $finestra_temporale);
        }
        
        $qb->orderBy('sede.data_creazione', 'asc');

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @param Procedura $procedura
     * @param null $finestra_temporale
     * @return array|int|string
     */
    public function getSediOperativeRichiestaConfermate(Procedura $procedura, $finestra_temporale = null)
    {
        $qb = $this->createQueryBuilder('sede')
            ->join('sede.richiesta', 'richiesta')
            ->join('richiesta.stato', 'stato')
            ->join('richiesta.proponenti', 'mandatario', 'with', 'mandatario.mandatario = 1')
            ->join('mandatario.soggetto', 'soggetto')
            ->where(
                'richiesta.procedura = :procedura',
                'stato.codice in (:stati)'
            );

        if ($finestra_temporale) {
            $qb->andWhere('richiesta.finestra_temporale = :finestra_temporale');
        }

        $qb->setParameter('procedura', $procedura);
        $qb->setParameter('stati',[
            StatoRichiesta::PRE_INVIATA_PA,
            StatoRichiesta::PRE_PROTOCOLLATA,
        ]);

        if ($finestra_temporale) {
            $qb->setParameter('finestra_temporale', $finestra_temporale);
        }

        $qb->orderBy('sede.data_creazione', 'asc');

        return $qb->getQuery()
            ->getResult();
    }
}
