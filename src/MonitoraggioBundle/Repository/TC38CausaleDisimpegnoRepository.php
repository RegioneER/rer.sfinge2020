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
class TC38CausaleDisimpegnoRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC38 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC38CausaleDisimpegno  e '
                . "where e.causale_disimpegno like :causale_disimpegno "
                . "and coalesce(e.descrizione_causale_disimpegno, '') like :descrizione_causale_disimpegno "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':causale_disimpegno', '%'.$ricerca->getCausaleDisimpegno().'%' );
        $q->setParameter(':descrizione_causale_disimpegno', '%'.$ricerca->getDescrizioneCausaleDisimpegno().'%' );


        return $q;
    }
}
