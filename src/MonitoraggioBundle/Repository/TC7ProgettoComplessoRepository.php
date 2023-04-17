<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Repository;
use Doctrine\ORM\EntityRepository;

/**
 * Description of TC7ProgettoComplessoRepository
 *
 * @author lfontana
 */
class TC7ProgettoComplessoRepository extends EntityRepository{
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\TabelleContesto\TC7 $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC7ProgettoComplesso e '
                . "where e.cod_prg_complesso like :cod_prg_complesso "
                    . "and coalesce(e.descrizione_progetto_complesso, '') like :descrizione_progetto_complesso "
                    . "and coalesce(e.cod_programma, '') like :cod_programma "
                    . "and coalesce(e.codice_tipo_complessita, '') like :codice_tipo_complessita "
                    . "and coalesce(e.descrizione_tipo_complessita, '') like :descrizione_tipo_complessita "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_prg_complesso', '%'.$ricerca->getCodPrgComplesso().'%' );
        $q->setParameter(':descrizione_progetto_complesso', '%'.$ricerca->getDescrizioneProgettoComplesso().'%' );
        $q->setParameter(':cod_programma', '%'.$ricerca->getCodProgramma().'%' );
        $q->setParameter(':codice_tipo_complessita', '%'.$ricerca->getCodiceTipoComplessita().'%' );
        $q->setParameter(':descrizione_tipo_complessita', '%'.$ricerca->getDescrizioneTipoComplessita().'%' );

        return $q;
    }
}
