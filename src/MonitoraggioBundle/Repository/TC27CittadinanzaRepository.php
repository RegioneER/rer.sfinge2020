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
class TC27CittadinanzaRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC27 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC27Cittadinanza  e '
                . "where e.cittadinanza like :cittadinanza "
                . "and coalesce(e.descrizione_cittadinanza, '') like :descrizione_cittadinanza "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cittadinanza', '%'.$ricerca->getCittadinanza().'%' );
        $q->setParameter(':descrizione_cittadinanza', '%'.$ricerca->getDescrizioneCittadinanza().'%' );


        return $q;
    }
}
