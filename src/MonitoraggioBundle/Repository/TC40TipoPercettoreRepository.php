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
class TC40TipoPercettoreRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC40 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC40TipoPercettore  e '
                . "where e.tipo_percettore like :tipo_percettore "
                . "and coalesce(e.descrizione_tipo_percettore, '') like :descrizione_tipo_percettore "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':tipo_percettore', '%'.$ricerca->getTipoPercettore().'%' );
        $q->setParameter(':descrizione_tipo_percettore', '%'.$ricerca->getDescrizioneTipoPercettore().'%' );


        return $q;
    }
}
