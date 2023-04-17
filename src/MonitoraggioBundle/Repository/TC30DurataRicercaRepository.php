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
class TC30DurataRicercaRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC30 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC30DurataRicerca  e '
                . "where e.durata_ricerca like :durata_ricerca "
                . "and coalesce(e.descrizione_durata_ricerca, '') like :descrizione_durata_ricerca "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':durata_ricerca', '%'.$ricerca->getDurataRicerca().'%' );
        $q->setParameter(':descrizione_durata_ricerca', '%'.$ricerca->getDescrizioneDurataRicerca().'%' );


        return $q;
    }
}
