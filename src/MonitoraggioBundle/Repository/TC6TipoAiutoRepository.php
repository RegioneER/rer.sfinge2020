<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Repository;
use Doctrine\ORM\EntityRepository;
/**
 * Description of TC6TipoAiutoRepository
 *
 * @author lfontana
 */
class TC6TipoAiutoRepository extends EntityRepository{
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC6 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC6TipoAiuto e '
                . "where e.tipo_aiuto like :tipo_aiuto "
                . "and coalesce(e.descrizione_tipo_aiuto, '') like :descrizione "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':tipo_aiuto', '%'.$ricerca->getTipoAiuto().'%' );
        $q->setParameter(':descrizione', '%'.$ricerca->getDescrizioneTipoAiuto().'%' );

        return $q;
    }
}
