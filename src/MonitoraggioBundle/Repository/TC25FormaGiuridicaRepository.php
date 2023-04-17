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
class TC25FormaGiuridicaRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC25 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC25FormaGiuridica e '
                . "where e.forma_giuridica like :forma_giuridica "
                . "and coalesce(e.descrizione_forma_giuridica, '') like :descrizione_forma_giuridica "
                . "and coalesce(e.divisione, '') like :divisione "
                . "and coalesce(e.sezione, '') like :sezione "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':forma_giuridica', '%'.$ricerca->getFormaGiuridica().'%' );
        $q->setParameter(':descrizione_forma_giuridica', '%'.$ricerca->getDescrizioneFormaGiuridica().'%' );
        $q->setParameter(':divisione', '%'.$ricerca->getDivisione().'%' );
        $q->setParameter(':sezione', '%'.$ricerca->getSezione().'%' );


        return $q;
    }
}
