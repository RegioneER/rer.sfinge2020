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
class TC45IndicatoriOutputProgrammaRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC45 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC45IndicatoriOutputProgramma  e '
                . 'left join e.programma programma with programma = coalesce(:programma, programma)'
                . "where e.cod_indicatore like :cod_indicatore "
                . "and coalesce(e.cod_indicatore_out, '') like :cod_indicatore_out "
                . "and coalesce(e.descrizione_indicatore, '') like :descrizione_indicatore "
                . "and coalesce(e.unita_misura, '') like :unita_misura "
                . "and coalesce(e.desc_unita_misura, '') like :desc_unita_misura "
                . "and coalesce(e.fonte_dato, '') like :fonte_dato ";

                if(!is_null($ricerca->getProgramma())){
                    $query .= "programma = :programma ";
                }
                   
                $query .= "order by e.id asc";
                
        $q->setDQL($query);
        $q->setParameter(':cod_indicatore', '%'.$ricerca->getCodIndicatore().'%' );
        $q->setParameter(':cod_indicatore_out', '%'.$ricerca->getCodIndicatoreOut().'%' );
        $q->setParameter(':descrizione_indicatore', '%'.$ricerca->getDescrizioneIndicatore().'%' );
        $q->setParameter(':unita_misura', '%'.$ricerca->getUnitaMisura().'%' );
        $q->setParameter(':desc_unita_misura', '%'.$ricerca->getDescUnitaMisura().'%' );
        $q->setParameter(':fonte_dato', '%'.$ricerca->getFonteDato().'%' );
        $q->setParameter(':programma', $ricerca->getProgramma() );


        return $q;
    }
}
