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
class TC21QualificaRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC21 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC21Qualifica e '
                . "where e.cod_amministrazione like :cod_amministrazione "
                . "and coalesce(e.descrizione_qualifica, '') like :descrizione_qualifica "
                . "and coalesce(e.cod_qualifica, '') like :cod_qualifica "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_amministrazione', '%'.$ricerca->getCodAmministrazione().'%' );
        $q->setParameter(':descrizione_qualifica', '%'.$ricerca->getDescrizioneQualifica().'%' );
        $q->setParameter(':cod_qualifica', '%'.$ricerca->getCodQualifica().'%' );


        return $q;
    }
}
