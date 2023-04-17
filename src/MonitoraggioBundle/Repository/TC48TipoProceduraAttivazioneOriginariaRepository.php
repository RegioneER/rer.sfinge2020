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
class TC48TipoProceduraAttivazioneOriginariaRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC48 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC48TipoProceduraAttivazioneOriginaria  e '
                . "where e.tip_proc_att_orig like :tip_proc_att_orig "
                . "and coalesce(e.descrizione_tipo_procedura_orig, '') like :descrizione_tipo_procedura_orig "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':tip_proc_att_orig', '%'.$ricerca->getTipProcAttOrig().'%' );
        $q->setParameter(':descrizione_tipo_procedura_orig', '%'.$ricerca->getDescrizioneTipoProceduraOrig().'%' );


        return $q;
    }
}
