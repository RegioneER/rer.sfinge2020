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
class TC29CondizioneMercatoRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC29 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC29CondizioneMercato  e '
                . "where e.cond_mercato_ingresso like :cond_mercato_ingresso "
                . "and coalesce(e.descrizione_condizione_mercato, '') like :descrizione_condizione_mercato "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cond_mercato_ingresso', '%'.$ricerca->getCondMercatoIngresso().'%' );
        $q->setParameter(':descrizione_condizione_mercato', '%'.$ricerca->getDescrizioneCondizioneMercato().'%' );


        return $q;
    }
}
