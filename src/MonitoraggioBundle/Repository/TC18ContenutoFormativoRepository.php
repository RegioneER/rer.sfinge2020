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
class TC18ContenutoFormativoRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC18 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC18ContenutoFormativo e '
                . "where e.cod_contenuto_formativo like :cod_contenuto_formativo "
                . "and coalesce(e.descrizione_contenuto_formativo, '') like :descrizione_contenuto_formativo "
                . "and coalesce(e.codice_settore, '') like :codice_settore "
                . "and coalesce(e.descrizione_settore, '') like :descrizione_settore "

                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_contenuto_formativo', '%'.$ricerca->getCodContenutoFormativo().'%' );
        $q->setParameter(':descrizione_contenuto_formativo', '%'.$ricerca->getDescrizioneContenutoFormativo().'%' );
        $q->setParameter(':codice_settore', '%'.$ricerca->getCodiceSettore().'%' );
        $q->setParameter(':descrizione_settore', '%'.$ricerca->getDescrizioneSettore().'%' );


        return $q;
    }
}
