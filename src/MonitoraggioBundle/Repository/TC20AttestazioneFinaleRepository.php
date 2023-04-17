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
class TC20AttestazioneFinaleRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC20 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC20AttestazioneFinale e '
                . "where e.cod_attestazione_finale like :cod_attestazione_finale "
                . "and coalesce(e.descrizione_attestazione_finale, '') like :descrizione_attestazione_finale "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_attestazione_finale', '%'.$ricerca->getCodAttestazioneFinale().'%' );
        $q->setParameter(':descrizione_attestazione_finale', '%'.$ricerca->getDescrizioneAttestazioneFinale().'%' );


        return $q;
    }
}
