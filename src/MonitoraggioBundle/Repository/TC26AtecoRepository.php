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
class TC26AtecoRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC26 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC26Ateco  e '
                . "where e.cod_ateco_anno like :cod_ateco_anno "
                . "and coalesce(e.descrizione_codice_ateco, '') like :descrizione_codice_ateco "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_ateco_anno', '%'.$ricerca->getCodAtecoAnno().'%' );
        $q->setParameter(':descrizione_codice_ateco', '%'.$ricerca->getDescrizioneCodiceAteco().'%' );


        return $q;
    }
}
