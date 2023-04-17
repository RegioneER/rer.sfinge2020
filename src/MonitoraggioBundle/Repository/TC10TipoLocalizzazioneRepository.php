<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Repository;
use Doctrine\ORM\EntityRepository;
/**
 * Description of TC10TipoLocalizzazioneRepository
 *
 * @author lfontana
 */
class TC10TipoLocalizzazioneRepository extends EntityRepository{
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC10 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC10TipoLocalizzazione e '
                . "where e.tipo_localizzazione like :tipo_localizzazione "
                    . "and coalesce(e.descrizione_tipo_localizzazione, '') like :descrizione_tipo_localizzazione "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':tipo_localizzazione', '%'.$ricerca->getTipoLocalizzazione().'%' );
        $q->setParameter(':descrizione_tipo_localizzazione', '%'.$ricerca->getDescrizioneTipoLocalizzazione(). '%' );

        return $q;
    }
}
