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
class TC33FonteFinanziariaRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC33 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC33FonteFinanziaria e '
                . "where e.cod_fondo like :cod_fondo "
                . "and coalesce(e.descrizione_fondo, '') like :descrizione_fondo "
                . "and coalesce(e.cod_fonte, '') like :cod_fonte "
                . "and coalesce(e.descrizione_fonte, '') like :descrizione_fonte "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_fondo', '%'.$ricerca->getCodFondo().'%' );
        $q->setParameter(':descrizione_fondo', '%'.$ricerca->getDescrizioneFondo().'%' );
        $q->setParameter(':cod_fonte', '%'.$ricerca->getCodFonte().'%' );
        $q->setParameter(':descrizione_fonte', '%'.$ricerca->getDescrizioneFonte().'%' );


        return $q;
    }
}
