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
class TC19CriterioSelezioneRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC19 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC19CriterioSelezione e '
                . "where e.cod_criterio_selezione like :cod_criterio_selezione "
                . "and coalesce(e.descrizione_criterio_selezione, '') like :descrizione_criterio_selezione "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_criterio_selezione', '%'.$ricerca->getCodCriterioSelezione().'%' );
        $q->setParameter(':descrizione_criterio_selezione', '%'.$ricerca->getDescrizioneCriterioSelezione().'%' );


        return $q;
    }
}
