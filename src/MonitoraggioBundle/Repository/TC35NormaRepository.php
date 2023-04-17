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
class TC35NormaRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC35 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC35Norma  e '
                . "where e.cod_norma like :cod_norma "
                . "and coalesce(e.tipo_norma, '') like :tipo_norma "
                . "and coalesce(e.descrizione_norma, '') like :descrizione_norma "
                . "and coalesce(e.numero_norma, '') like :numero_norma "
                . "and coalesce(e.anno_norma, '') like :anno_norma "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_norma', '%'.$ricerca->getCodNorma().'%' );
        $q->setParameter(':tipo_norma', '%'.$ricerca->getTipoNorma().'%' );
        $q->setParameter(':descrizione_norma', '%'.$ricerca->getDescrizioneNorma().'%' );
        $q->setParameter(':numero_norma', '%'.$ricerca->getNumeroNorma().'%' );
        $q->setParameter(':anno_norma', '%'.$ricerca->getAnnoNorma().'%' );


        return $q;
    }
}
