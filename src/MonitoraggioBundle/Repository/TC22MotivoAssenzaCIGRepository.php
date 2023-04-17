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
class TC22MotivoAssenzaCIGRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC22 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC22MotivoAssenzaCIG e '
                . "where e.motivo_assenza_cig like :motivo_assenza_cig "
                . "and coalesce(e.desc_motivo_assenza_cig, '') like :desc_motivo_assenza_cig "

                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':motivo_assenza_cig', '%'.$ricerca->getMotivoAssenzaCig().'%' );
        $q->setParameter(':desc_motivo_assenza_cig', '%'.$ricerca->getDescMotivoAssenzaCig().'%' );


        return $q;
    }
}
