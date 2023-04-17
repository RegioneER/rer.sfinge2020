<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Repository;
use Doctrine\ORM\EntityRepository;
/**
 * Description of TC8GrandeProgettoRepository
 *
 * @author lfontana
 */
class TC8GrandeProgettoRepository extends EntityRepository{
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC8 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC8GrandeProgetto e '
                . 'join e.programma programma '
                . "where e.grande_progetto like :grande_progetto "
                    . "and coalesce(e.descrizione_grande_progetto, '') like :descrizione_grande_progetto "
                    . "and coalesce(programma.id,0) = coalesce(:cod_programma, programma.id,0) "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':grande_progetto', '%'.$ricerca->getGrandeProgetto().'%' );
        $q->setParameter(':descrizione_grande_progetto', '%'.$ricerca->getDescrizioneGrandeProgetto(). '%' );
        $q->setParameter(':cod_programma', (is_null($ricerca->getCodProgramma()) ? null: $ricerca->getCodProgramma()->getId() ));

        return $q;
    }
}
