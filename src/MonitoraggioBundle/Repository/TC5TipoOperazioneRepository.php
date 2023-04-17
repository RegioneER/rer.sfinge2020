<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Repository;

use Doctrine\ORM\EntityRepository;
/**
 * Description of TC5TipoOperazioneRepository
 *
 * @author lfontana
 */
class TC5TipoOperazioneRepository extends EntityRepository{
    
public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC5 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC5TipoOperazione e '
                . "where e.tipo_operazione like :tipo_operazione "
                . "and coalesce(e.codice_natura_cup, '') like :codice_natura_cup "
                . "and coalesce(e.descrizione_natura_cup, '') like :descrizione_natura_cup "
                . "and coalesce(e.codice_tipologia_cup, '') like :codice_tipologia_cup "
                . "and coalesce(e.descrizione_tipologia_cup, '') like :descrizione_tipologia_cup "
                . "and coalesce(e.origine_dato, '') like :origine_dato "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':tipo_operazione', '%'.$ricerca->getTipoOperazione().'%' );
        $q->setParameter(':codice_natura_cup', '%'.$ricerca->getCodiceNaturaCup().'%' );
        $q->setParameter(':descrizione_natura_cup', '%'.$ricerca->getDescrizioneNaturaCup().'%' );
        $q->setParameter(':codice_tipologia_cup', '%'.$ricerca->getCodiceTipologiaCup().'%' );
        $q->setParameter(':descrizione_tipologia_cup', '%'.$ricerca->getDescrizioneTipologiaCup().'%' );
        $q->setParameter(':origine_dato', '%'.$ricerca->getOrigineDato().'%' );

        return $q;
    }
}
