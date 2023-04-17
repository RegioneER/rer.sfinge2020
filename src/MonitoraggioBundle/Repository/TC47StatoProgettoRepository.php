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
class TC47StatoProgettoRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC47 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC47StatoProgetto  e '
                . "where e.stato_progetto like :stato_progetto "
                . "and coalesce(e.descr_stato_prg, '') like :descr_stato_prg "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':stato_progetto', '%'.$ricerca->getStatoProgetto().'%' );
        $q->setParameter(':descr_stato_prg', '%'.$ricerca->getDescrStatoPrg().'%' );


        return $q;
    }
}
