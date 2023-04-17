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
class TC24RuoloSoggettoRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC24 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC24RuoloSoggetto e '
                . "where e.cod_ruolo_sog like :cod_ruolo_sog "
                . "and coalesce(e.descrizione_ruolo_soggetto, '') like :descrizione_ruolo_soggetto "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_ruolo_sog', '%'.$ricerca->getCodRuoloSog().'%' );
        $q->setParameter(':descrizione_ruolo_soggetto', '%'.$ricerca->getDescrizioneRuoloSoggetto().'%' );


        return $q;
    }
}
