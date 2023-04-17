<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

/**
 * @author afavilli
 */
class PG00ProcedureAggiudicazioneRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\PG00 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select PG00 '
                . 'from MonitoraggioBundle:PG00ProcedureAggiudicazione PG00 '
                . 'join PG00.tc22_motivo_assenza_cig tc22 '
                . 'join PG00.tc23_tipo_procedura_aggiudicazione tc23 '
                . "where coalesce(PG00.cod_locale_progetto, '') like :cod_locale_progetto "
                . 'and coalesce(PG00.cod_proc_agg) like :cod_proc_agg '
                . 'and coalesce(PG00.cig) like :cig '
                . 'and tc22 = coalesce(:tc22_motivo_assenza_cig, tc22) '
                . 'and tc23 = coalesce(:tc23_tipo_procedura_aggiudicazione, tc23) '
                . 'and coalesce(PG00.importo_procedura_agg,0) = coalesce(:importo_procedura_agg, PG00.importo_procedura_agg,0) '
                . "and coalesce(PG00.data_pubblicazione, '9999-12-31') = coalesce(:data_pubblicazione, PG00.data_pubblicazione, '9999-12-31' ) "
                . 'and coalesce(PG00.importo_aggiudicato,0) = coalesce(:importo_aggiudicato, PG00.importo_aggiudicato, 0) '
                . "and coalesce(PG00.data_aggiudicazione, '9999-12-31') = coalesce(:data_aggiudicazione, PG00.data_aggiudicazione, '9999-12-31') "
                . "and coalesce(PG00.flg_cancellazione, '') = coalesce(:flg_cancellazione, PG00.flg_cancellazione, '') "
                . 'order by PG00.id asc';
        $q->setDQL($query);

        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':cod_proc_agg', '%' . $ricerca->getCodProcAgg() . '%');
        $q->setParameter(':cig', '%' . $ricerca->getCig() . '%');
        $q->setParameter(':tc22_motivo_assenza_cig', $ricerca->getTc22MotivoAssenzaCig());
        $q->setParameter(':tc23_tipo_procedura_aggiudicazione', $ricerca->getTc23TipoProceduraAggiudicazione());
        $q->setParameter(':importo_procedura_agg', $ricerca->getImportoProceduraAgg());

        $q->setParameter(':data_pubblicazione', $ricerca->getDataPubblicazione());
        $q->setParameter(':importo_aggiudicato', $ricerca->getImportoAggiudicato());
        $q->setParameter(':data_aggiudicazione', $ricerca->getDataAggiudicazione());

        $q->setParameter(':flg_cancellazione', $ricerca->getFlgCancellazione());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();
        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_procedure_aggiudicazione', 'procedure_aggiudicazione')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(procedure_aggiudicazione.data_modifica, procedure_aggiudicazione.data_creazione,'0000-00-00')")
        )
        ->getQuery()
        ->getSingleResult();
        $dataEsportazione = \array_pop($res);

        return \is_null($dataEsportazione);
    }

    public function findAllEsportabili($date) {
        $query = 'select distinct richiesta '
        . 'from RichiesteBundle:Richiesta richiesta '
        . 'join richiesta.istruttoria istruttoria '
        . 'join richiesta.attuazione_controllo attuazione_controllo '
        . 'join richiesta.mon_procedure_aggiudicazione mon_procedure_aggiudicazione '

        . 'where (coalesce(richiesta.data_modifica, richiesta.data_creazione) > :data_esportazione '
                . 'or coalesce(attuazione_controllo.data_modifica, attuazione_controllo.data_creazione) > :data_esportazione '
                . 'or coalesce(mon_procedure_aggiudicazione.data_modifica, mon_procedure_aggiudicazione.data_creazione) > :data_esportazione '
                . 'or coalesce(istruttoria.data_modifica, istruttoria.data_creazione) > :data_esportazione) AND COALESCE(richiesta.flag_por, 0) = 1 AND COALESCE(richiesta.flag_inviato_monit, 0) = 0';

        return $this->getEntityManager()
                ->createQuery($query)
                ->setParameter('data_esportazione', $date)
                ->iterate();
    }
}
