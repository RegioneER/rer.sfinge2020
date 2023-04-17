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
class TC37VoceSpesaRepository extends EntityRepository{
    
     public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC37 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC37VoceSpesa  e '
                . "where e.voce_spesa like :voce_spesa "
                . "and coalesce(e.descrizione_voce_spesa, '') like :descrizione_voce_spesa "
                . "and coalesce(e.codice_natura_cup, '') like :codice_natura_cup "
                . "and coalesce(e.descrizionenatura_cup, '') like :descrizionenatura_cup "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':voce_spesa', '%'.$ricerca->getVoceSpesa().'%' );
        $q->setParameter(':descrizione_voce_spesa', '%'.$ricerca->getDescrizioneVoceSpesa().'%' );
        $q->setParameter(':codice_natura_cup', '%'.$ricerca->getCodiceNaturaCup().'%' );
        $q->setParameter(':descrizionenatura_cup', '%'.$ricerca->getDescrizionenaturaCup().'%' );


        return $q;
    }
}
