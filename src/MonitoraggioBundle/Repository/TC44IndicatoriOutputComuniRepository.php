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
class TC44IndicatoriOutputComuniRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC44 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC44IndicatoriOutputComuni  e '
                . "where e.cod_indicatore like :cod_indicatore "
                . "and coalesce(e.descrizione_indicatore, '') like :descrizione_indicatore "
                . "and coalesce(e.unita_misura, '') like :unita_misura "
                . "and coalesce(e.desc_unita_misura, '') like :desc_unita_misura "
                . "and coalesce(e.flag_calcolo, '') like :flag_calcolo "
                . "and coalesce(e.fonte_dato, '') like :fonte_dato "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_indicatore', '%'.$ricerca->getCodIndicatore().'%' );
        $q->setParameter(':descrizione_indicatore', '%'.$ricerca->getDescrizioneIndicatore().'%' );
        $q->setParameter(':unita_misura', '%'.$ricerca->getUnitaMisura().'%' );
        $q->setParameter(':desc_unita_misura', '%'.$ricerca->getDescUnitaMisura().'%' );
        $q->setParameter(':flag_calcolo', '%'.$ricerca->getFlagCalcolo().'%' );
        $q->setParameter(':fonte_dato', '%'.$ricerca->getFonteDato().'%' );


        return $q;
    }
}
