<?php

namespace MonitoraggioBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase;
use Doctrine\ORM\QueryBuilder;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Procedura;
use MonitoraggioBundle\Entity\Trasferimento;


class EsportazioneProgettoRepository extends EntityRepository
{
    protected function getQueryEsportazioneRichiesta(Richiesta $richiesta): QueryBuilder{
        $qb = $this->createQueryBuilder('struttura');
        $expr = $qb->expr();
        return $qb
        ->select("max(coalesce(struttura.data_modifica, struttura.data_creazione, '0000-00-00'))")
        ->join('MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneTavole', 'tavola', Expr\Join::WITH, $expr->eq('tavola.id', 'struttura.monitoraggio_configurazione_esportazioni_tavola'))
        ->join('MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneRichiesta', 'configurazione', Expr\Join::WITH, $expr->eq('configurazione.id', 'tavola.monitoraggio_configurazione_esportazione'))
        ->join('configurazione.monitoraggio_esportazione','esportazione')
        ->join('esportazione.fasi', 'fasi')
        ->join('configurazione.richiesta', 'richiesta')
        ->where(
            $expr->in('fasi.fase',':statoCompletato, :statoImportato'),
            $expr->eq('tavola.flag_conferma_esportazione', 1),
            $expr->eq('richiesta.id', ':id_richiesta')
        )
        ->setParameter('statoCompletato', MonitoraggioEsportazioneLogFase::STATO_COMPLETATO)
        ->setParameter('statoImportato', MonitoraggioEsportazioneLogFase::STATO_IMPORTATO)
        ->setParameter('id_richiesta', $richiesta->getId())
        ;
    }

    protected function getQueryEsportazioneProcedura(Procedura $procedura) :QueryBuilder{
        $qb = $this->createQueryBuilder('struttura');
        $expr = $qb->expr();
        return $qb
        ->join('MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneTavole', 'tavola', Expr\Join::WITH, $expr->eq('tavola.id', 'struttura.monitoraggio_configurazione_esportazioni_tavola'))
        ->join('MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneProcedura', 'configurazione', Expr\Join::WITH, $expr->eq('configurazione.id', 'tavola.monitoraggio_configurazione_esportazione'))
        ->join('configurazione.monitoraggio_esportazione','esportazione')
        ->join('esportazione.fasi', 'fasi')
        ->join('configurazione.procedura', 'procedura')
        ->where(
            $expr->in('fasi.fase',':statoCompletato, :statoImportato'),
            $expr->eq('tavola.flag_conferma_esportazione', 1),
            $expr->eq('procedura.id', ':id_procedura')
        )
        ->setParameter('statoCompletato', MonitoraggioEsportazioneLogFase::STATO_COMPLETATO)
        ->setParameter('statoImportato', MonitoraggioEsportazioneLogFase::STATO_IMPORTATO)
        ->setParameter('id_procedura', $procedura->getId())
        ;
    }

    protected function getQueryEsportazioneTrasferimento(Trasferimento $trasferimento) :QueryBuilder{
        $qb = $this->createQueryBuilder('struttura');
        $expr = $qb->expr();
        return $qb
        ->join('MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneTavole', 'tavola', Expr\Join::WITH, $expr->eq('tavola.id', 'struttura.monitoraggio_configurazione_esportazioni_tavola'))
        ->join('MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneTrasferimento', 'configurazione', Expr\Join::WITH, $expr->eq('configurazione.id', 'tavola.monitoraggio_configurazione_esportazione'))
        ->join('configurazione.monitoraggio_esportazione','esportazione')
        ->join('esportazione.fasi', 'fasi')
        ->join('configurazione.trasferimento', 'trasferimento')
        ->where(
            $expr->in('fasi.fase',':statoCompletato, :statoImportato'),
            $expr->eq('tavola.flag_conferma_esportazione', 1),
            $expr->eq('trasferimento.id', ':id_trasferimento')
        )
        ->setParameter('statoCompletato', MonitoraggioEsportazioneLogFase::STATO_COMPLETATO)
        ->setParameter('statoImportato', MonitoraggioEsportazioneLogFase::STATO_IMPORTATO)
        ->setParameter('id_trasferimento', $trasferimento->getId())
        ;
    }

    protected function maxElementResultArray(array $result):\DateTime{
        return \array_reduce($result, function(\DateTime $carry, $el){
            if(\is_null($el)){
                return $carry;
            }
            $el = new \DateTime($el);
            return $el > $carry ? $el : $carry;
        }, new \DateTime('0000-00-00'));
    }
}