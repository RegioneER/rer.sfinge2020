<?php

namespace MonitoraggioBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MonitoraggioBundle\Form\Entity\RicercaEsportazioneProgetto;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;


class MonitoraggioConfigurazioneEsportazioneRichiestaRepository extends EntityRepository
{
    protected function queryBuilderRicercaProgettiEsportabili():QueryBuilder{
        $qb = $this->createQueryBuilder('configurazione');
        $expr = $qb->expr();

        $qb
        
        ->join('configurazione.monitoraggio_configurazione_esportazione_tavole', 'tavola')
        ->join('configurazione.richiesta', 'richiesta')
        ->join('richiesta.procedura', 'procedura')
        ->join('richiesta.richieste_protocollo', 'protocollo')
        ->join('configurazione.monitoraggio_esportazione','esportazione')
        ->where(
            $expr->isNull('configurazione.data_cancellazione'),
            $expr->isNull('esportazione.data_cancellazione')
        );
        return $qb;
    }
    
    public function ricercaProgettiEsportabili(RicercaEsportazioneProgetto $filtro): Query
    {
        $qb = $this->queryBuilderRicercaProgettiEsportabili();
        $expr = $qb->expr();
        return $qb
        ->select('configurazione','tavola', 'errori')
        ->leftJoin('configurazione.monitoraggio_configurazione_esportazione_errori', 'errori')
        ->andWhere(
            $expr->eq('procedura.id', 'coalesce(:procedura, procedura.id)'),
            $expr->eq('esportazione.id', ':esportazione_id'),
            $expr->like(
                new Expr\Func('CONCAT', [
                    'protocollo.registro_pg',
                    $expr->literal('/'),
                    'protocollo.anno_pg',
                    $expr->literal('/'),
                    'protocollo.num_pg'
                ]),
                new Expr\Func('CONCAT', [
                    $expr->literal('%'),
                    "coalesce(:protocollo,'')",
                    $expr->literal('%'),
                ])
            )
        )
        ->setParameter('procedura', $filtro->procedura ? $filtro->procedura->getId(): null )
        ->setParameter('esportazione_id', $filtro->esportazione->getId())
        ->setParameter('protocollo', $filtro->protocollo)
        ->getQuery();
    }
}