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
class TC31GruppoVulnerabilePartecipanteRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC31 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC31GruppoVulnerabilePartecipante  e '
                . "where e.codice_vulnerabile_pa like :codice_vulnerabile_pa "
                . "and coalesce(e.descr_vulnerabile_pa, '') like :descr_vulnerabile_pa "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':codice_vulnerabile_pa', '%'.$ricerca->getCodiceVulnerabilePa().'%' );
        $q->setParameter(':descr_vulnerabile_pa', '%'.$ricerca->getDescrVulnerabilePa().'%' );


        return $q;
    }
}
