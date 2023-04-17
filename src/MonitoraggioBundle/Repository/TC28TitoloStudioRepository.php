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
class TC28TitoloStudioRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC28 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC28TitoloStudio  e '
                . "where e.titolo_studio like :titolo_studio "
                . "and coalesce(e.descrizione_titolo_studio, '') like :descrizione_titolo_studio "
                . "and coalesce(e.isced, '') like :isced "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':titolo_studio', '%'.$ricerca->getTitoloStudio().'%' );
        $q->setParameter(':descrizione_titolo_studio', '%'.$ricerca->getDescrizioneTitoloStudio().'%' );
        $q->setParameter(':isced', '%'.$ricerca->getIsced().'%' );


        return $q;
    }
}
