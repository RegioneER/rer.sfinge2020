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
class TC42IndicatoriRisultatoComuniRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC42 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC42IndicatoriRisultatoComuni  e '
                . "where e.cod_indicatore like :cod_indicatore "
                . "and coalesce(e.descrizione_indicatore, '') like :descrizione_indicatore "
                . "and coalesce(e.fonte_dato, '') like :fonte_dato "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_indicatore', '%'.$ricerca->getCodIndicatore().'%' );
        $q->setParameter(':descrizione_indicatore', '%'.$ricerca->getDescrizioneIndicatore().'%' );
        $q->setParameter(':fonte_dato', '%'.$ricerca->getFonteDato().'%' );


        return $q;
    }
}
