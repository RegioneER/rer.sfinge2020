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
class TC14SpecificaStatoRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC14 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC14SpecificaStato e '
                . "where e.specifica_stato like :specifica_stato "
                . "and coalesce(e.desc_specifica_stato, '') like :desc_specifica_stato "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':specifica_stato', '%'.$ricerca->getSpecificaStato().'%' );
        $q->setParameter(':desc_specifica_stato', '%'.$ricerca->getDescSpecificaStato().'%' );

        return $q;
    }
}
