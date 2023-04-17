<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Repository;
use Doctrine\ORM\EntityRepository;
/**
 * Description of TC3ResponsabileProceduraRepository
 *
 * @author lfontana
 */
class TC32StatoPartecipanteRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC32 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC32StatoPartecipante e '
                . "where e.stato_partecipante like :stato_partecipante "
                . "and coalesce(e.descrizione_stato_partecipante, '') like :descrizione_stato_partecipante "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':stato_partecipante', '%'.$ricerca->getStatoPartecipante().'%' );
        $q->setParameter(':descrizione_stato_partecipante', '%'.$ricerca->getDescrizioneStatoPartecipante().'%' );


        return $q;
    }
}
