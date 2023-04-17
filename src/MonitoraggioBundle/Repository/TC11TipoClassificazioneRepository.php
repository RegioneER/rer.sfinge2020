<?php

namespace MonitoraggioBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class TC11TipoClassificazioneRepository extends EntityRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC11 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC11TipoClassificazione e '
                . "where e.tipo_class like :tipo_class "
                    . "and coalesce(e.descrizione_tipo_classificazione, '') like :descrizione_tipo_classificazione "
                    . "and coalesce(e.origine_classificazione, '') like :origine_classificazione "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':tipo_class', '%' . $ricerca->getTipoClass() . '%');
        $q->setParameter(':descrizione_tipo_classificazione', '%' . $ricerca->getDescrizioneTipoClassificazione() . '%');
        $q->setParameter(':origine_classificazione', '%' . $ricerca->getOrigineClassificazione() . '%');

        return $q;
    }

    /**
     * @return QueryBuilder
     */
    public function tipiConClassificazione() {
        $qb = $this->createQueryBuilder('tipi');
        $expr = $qb->expr();
        return $qb
        ->join('tipi.classificazioni', 'classificazioni')
        ->leftJoin('classificazioni.programma', 'programma')
        ->join('classificazioni.azioni', 'azioni')
        ->join('azioni.procedure', 'procedure')
        ->join('procedure.richieste', 'richieste')
        ->join('richieste.mon_programmi', 'richieste_programmi')
        ->join('richieste_programmi.tc4_programma','programma_richiesta',
            'with',
            'programma_richiesta = COALESCE(programma, programma_richiesta)');
    }
}
