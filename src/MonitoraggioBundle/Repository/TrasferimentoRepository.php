<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Entity\Trasferimento;

/**
 * Description of TrasferimentoRepository
 *
 * @author vbuscemi
 */
class TrasferimentoRepository extends EntityRepository {

     public function getTrasferimenti(\MonitoraggioBundle\Form\Entity\RicercaTrasferimento $ricerca){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select t '
                . 'from MonitoraggioBundle:Trasferimento t '
                . 'join t.bando bando with bando = coalesce(:bando, bando) '
                . 'join t.soggetto soggetto '
                . 'join t.causale_trasferimento causale_trasferimento with causale_trasferimento = coalesce(:causale_trasferimento, causale_trasferimento) '
                . "where coalesce(t.cod_trasferimento, '') like :cod_trasferimento "
                . "and coalesce(soggetto.denominazione, '') like :soggetto "
                //. "and coalesce(t.data_trasferimento, '9999-12-31') = coalesce(:data_trasferimento, t.data_trasferimento, '9999-12-31') "
                . "and t.importo_trasferimento = coalesce(:importo_trasferimento, t.importo_trasferimento) "
                . "order by t.id asc";
        $q->setDQL($query);
        $q->setParameter(':bando', $ricerca->getBando() );
        $q->setParameter(':soggetto', '%'.$ricerca->getSoggetto().'%' );
        $q->setParameter(':causale_trasferimento', $ricerca->getCausaleTrasferimento() );
        $q->setParameter(':cod_trasferimento', '%'.$ricerca->getCodTrasferimento().'%' );
        //$q->setParameter(':data_trasferimento',  !is_null($ricerca->getDataTrasferimento()) ? $ricerca->getDataTrasferimento()->format('Y-m-d') : null);
        $q->setParameter(':importo_trasferimento', $ricerca->getImportoTrasferimento() );

        return $q;
    }
    
    public function cercaSoggetti($searchSoggetto){
        $q = $this->getEntityManager()->createQuery();
        $query = 'select s.id id, s.denominazione text '
                . 'from SoggettoBundle:Soggetto s '
                . "where s.denominazione like :soggetto "
                . "order by s.denominazione asc";
        $q->setDQL($query);
        $q->setParameter(':soggetto', '%'.$searchSoggetto.'%' );
        return $q->getResult();
    }

    /**
     * @param string $codiceTrasferimento
     * @param \DateTime $dataTrasferimento
     * @param TC4Programma $programma
     * @return Trasferimento|null
     */
    public function findOneByChiaveProtocolloIgrue($codiceTrasferimento, \DateTime $dataTrasferimento, TC4Programma $programma ){
        $dql = 'select trasferimento '
        .'from MonitoraggioBundle:Trasferimento trasferimento '
        .'where trasferimento.programma = :programma '
        .'and trasferimento.data_trasferimento = :data '
        .'and trasferimento.cod_trasferimento = :codice';

        $this->getEntityManager()
        ->createQuery($dql)
        ->setParameter('programma', $programma)
        ->setParameter('data', $dataTrasferimento)
        ->setParameter('codice', $codiceTrasferimento)
        ->getOneOrNullResult();
    }
}
