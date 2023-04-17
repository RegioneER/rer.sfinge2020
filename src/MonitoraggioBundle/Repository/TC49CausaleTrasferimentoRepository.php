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
class TC49CausaleTrasferimentoRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC49 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC49CausaleTrasferimento  e '
                . "where e.causale_trasferimento like :causale_trasferimento "
                . "and coalesce(e.descrizione_causale_trasferimento, '') like :descrizione_causale_trasferimento "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':causale_trasferimento', '%'.$ricerca->getCausaleTrasferimento().'%' );
        $q->setParameter(':descrizione_causale_trasferimento', '%'.$ricerca->getDescrizioneCausaleTrasferimento().'%' );


        return $q;
    }
}
