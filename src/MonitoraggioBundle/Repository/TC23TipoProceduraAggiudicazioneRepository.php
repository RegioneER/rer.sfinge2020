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
class TC23TipoProceduraAggiudicazioneRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC23 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC23TipoProceduraAggiudicazione e '
                . "where e.tipo_proc_agg like :tipo_proc_agg "
                . "and coalesce(e.descrizione_tipologia_procedura_aggiudicazione, '') like :descrizione_tipologia_procedura_aggiudicazione "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':tipo_proc_agg', '%'.$ricerca->getTipoProcAgg().'%' );
        $q->setParameter(':descrizione_tipologia_procedura_aggiudicazione', '%'.$ricerca->getDescrizioneTipologiaProceduraAggiudicazione().'%' );


        return $q;
    }
}
