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
class TC41DomandaPagamentoRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC41 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC41DomandaPagamento  e '
                . 'left join e.programma programma with programma = coalesce(:programma, programma) '
                . 'left join e.fondo fondo with fondo = coalesce(:fondo, fondo) '
                . "where e.id_domanda_pagamento like :id_domanda_pagamento ";
                
                if(!is_null($ricerca->getProgramma())){
                    $query .= "and programma =:programma ";
                }
        
                if(!is_null($ricerca->getFondo())){
                    $query .= "and fondo = :fondo ";
                }
                
                $query .= "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':id_domanda_pagamento', '%'.$ricerca->getIdDomandaPagamento().'%' );
        $q->setParameter(':programma', $ricerca->getProgramma() );
        $q->setParameter(':fondo', $ricerca->getFondo() );


        return $q;
    }
}
