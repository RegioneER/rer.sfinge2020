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
class TC4ProgrammaRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC4 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC4Programma e '
                . "where e.cod_programma like :codProgramma "
                . "and coalesce(e.descrizione_programma, '') like :descrizioneProgramma "
                . "and coalesce(e.fondo, '') like :fondo "
                . "and coalesce(e.codice_tipologia_programma, '') like :codTipologiaProgramma "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':codProgramma', '%'.$ricerca->getCodProgramma().'%' );
        $q->setParameter(':descrizioneProgramma', '%'.$ricerca->getDescrizioneProgramma().'%' );
        $q->setParameter(':fondo', '%'.$ricerca->getFondo().'%' );
        $q->setParameter(':codTipologiaProgramma', '%'.$ricerca->getCodiceTipologiaProgramma().'%' );

        return $q;
    }
}
