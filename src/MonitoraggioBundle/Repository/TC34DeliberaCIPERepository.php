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
class TC34DeliberaCIPERepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC34 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC34DeliberaCIPE  e '
                . "where e.cod_del_cipe like :cod_del_cipe "
                . "and coalesce(e.numero, '') like :numero "
                . "and coalesce(e.anno, '') like :anno "
                . "and coalesce(e.tipo_quota, '') like :tipo_quota "
                . "and coalesce(e.descrizione_quota, '') like :descrizione_quota "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_del_cipe', '%'.$ricerca->getCodDelCipe().'%' );
        $q->setParameter(':anno', '%'.$ricerca->getAnno().'%' );
        $q->setParameter(':tipo_quota', '%'.$ricerca->getTipoQuota().'%' );
        $q->setParameter(':descrizione_quota', '%'.$ricerca->getDescrizioneQuota().'%' );
        $q->setParameter(':numero', '%'.$ricerca->getNumero().'%' );


        return $q;
    }
}
