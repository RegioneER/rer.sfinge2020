<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Repository;
use Doctrine\ORM\EntityRepository;
/**
 * Description of TC1ProceduraAttivazioneRepository
 *
 * @author lfontana
 */
class TC1ProceduraAttivazioneRepository extends EntityRepository{

    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC1 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        
       
        $query = 'select pa '
            . 'from MonitoraggioBundle\Entity\TC1ProceduraAttivazione pa '
            . 'join pa.tip_procedura_att tip_procedura_att '
            . "where pa.cod_proc_att_locale like :procedura "
                . "and coalesce(pa.stato, '') = coalesce(:stato, pa.stato, '') "
                . "and coalesce(pa.descr_procedura_att, '') = coalesce(:descrizione, pa.descr_procedura_att, '' ) "
                . "and tip_procedura_att = coalesce(:tip_procedura_att, tip_procedura_att)"
            . "order by pa.id asc";
        
        $q->setDQL($query);
        $q->setParameter(':procedura', '%' . $ricerca->getCodProcAttLocale() . '%' );
        $q->setParameter(':stato', $ricerca->getStato() );
        $q->setParameter(':tip_procedura_att', $ricerca->getTipProceduraAtt() );
        $q->setParameter(':descrizione', $ricerca->getDescrProceduraAtt());

        return $q;
    }
}
