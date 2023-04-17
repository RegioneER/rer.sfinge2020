<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Repository;
use Doctrine\ORM\EntityRepository;
/**
 * Description of TC2TipoProceduraAttivazioneRepository
 *
 * @author lfontana
 */
class TC2TipoProceduraAttivazioneRepository extends EntityRepository {
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC2 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC2TipoProceduraAttivazione e '
                . "where e.tip_procedura_att like :codice "
                . "and coalesce(e.cod_proc_att_locale, '') like :descrizione "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':descrizione', '%'.$ricerca->getCodProcAttLocale().'%' );
        $q->setParameter(':codice', '%'.$ricerca->getTipProceduraAtt().'%' );
        return $q;
    }
}
