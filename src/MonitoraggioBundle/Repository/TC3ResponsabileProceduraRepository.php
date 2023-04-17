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
class TC3ResponsabileProceduraRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC3 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC3ResponsabileProcedura e '
                . "where e.cod_tipo_resp_proc like :codice "
                . "and coalesce(e.descrizione_responsabile_procedura, '') like :descrizione "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':descrizione', '%'.$ricerca->getDescrizioneResponsabileProcedura().'%' );
        $q->setParameter(':codice', '%'.$ricerca->getCodTipoRespProc().'%' );
        return $q;
    }
}
