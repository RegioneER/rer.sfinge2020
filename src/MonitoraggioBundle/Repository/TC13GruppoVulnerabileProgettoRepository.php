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
class TC13GruppoVulnerabileProgettoRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC13 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC13GruppoVulnerabileProgetto e '
                . "where e.cod_vulnerabili like :cod_vulnerabili "
                . "and coalesce(e.desc_vulnerabili, '') like :desc_vulnerabili "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_vulnerabili', '%'.$ricerca->getCodVulnerabili().'%' );
        $q->setParameter(':desc_vulnerabili', '%'.$ricerca->getDescVulnerabili().'%' );

        return $q;
    }
}
