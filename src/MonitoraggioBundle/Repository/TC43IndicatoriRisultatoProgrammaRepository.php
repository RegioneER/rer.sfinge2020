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
class TC43IndicatoriRisultatoProgrammaRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC43 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC43IndicatoriRisultatoProgramma  e '
                . 'left join e.programma programma with programma = coalesce(:programma, programma) '
                . "where e.cod_indicatore like :cod_indicatore "
                . "and coalesce(e.cod_indicatore_ris, '') like :cod_indicatore_ris "
                . "and coalesce(e.descrizione_indicatore, '') like :descrizione_indicatore "
                . "and coalesce(e.fonte_dato, '') like :fonte_dato ";
        
        if(!is_null($ricerca->getProgramma())){
            $query .= "and programma = :programma ";
        }
        
        $query .= "order by e.id asc";
        
        $q->setDQL($query);
        $q->setParameter(':cod_indicatore', '%'.$ricerca->getCodIndicatore().'%' );
        $q->setParameter(':cod_indicatore_ris', '%'.$ricerca->getCodIndicatoreRis().'%' );
        $q->setParameter(':descrizione_indicatore', '%'.$ricerca->getDescrizioneIndicatore().'%' );
        $q->setParameter(':fonte_dato', '%'.$ricerca->getFonteDato().'%' );
        $q->setParameter(':programma', $ricerca->getProgramma() );


        return $q;
    }
}
