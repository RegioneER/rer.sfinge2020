<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneProcedura;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;

/**
 * @author afavilli, lfontana
 */
class PA00ProcedureAttivazioneRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\PA00 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select PA00 '
            . 'from MonitoraggioBundle:PA00ProcedureAttivazione PA00 '

            . 'join PA00.tc2_tipo_procedura_attivazione tc2 '
            . 'join PA00.tc3_responsabile_procedura tc3 '

            . "where coalesce(PA00.cod_proc_att, '') like :cod_proc_att "
                . "and coalesce(PA00.cod_proc_att_locale, '') like :cod_proc_att_locale "
                . 'and coalesce(PA00.cod_aiuto_rna) = coalesce(:cod_aiuto_rna, PA00.cod_aiuto_rna) '
                . 'and tc2 = coalesce(:tc2_tipo_procedura_attivazione,tc2) '
                . "and coalesce(PA00.flag_aiuti, '') like  :flag_aiuti "
                . "and coalesce(PA00.descr_procedura_att, '') like  :descr_procedura_att "
                . 'and tc3 = coalesce(:tc3_responsabile_procedura,tc3) '
                . "and coalesce(PA00.data_avvio_procedura, '9999-12-31') = coalesce(:data_avvio_procedura, PA00.data_avvio_procedura, '9999-12-31') "
                . "and coalesce(PA00.data_fine_procedura, '9999-12-31') = coalesce(:data_fine_procedura, PA00.data_fine_procedura, '9999-12-31') "
                . "and coalesce(PA00.flg_cancellazione, '') = coalesce(:flg_cancellazione, PA00.flg_cancellazione, '') "
            . 'order by PA00.id asc';
        $q->setDQL($query);

        $q->setParameter(':cod_proc_att', '%' . $ricerca->getCodProcAtt() . '%');
        $q->setParameter(':cod_proc_att_locale', '%' . $ricerca->getCodProcAttLocale() . '%');
        $q->setParameter(':cod_aiuto_rna', $ricerca->getCodAiutoRna());
        $q->setParameter(':tc2_tipo_procedura_attivazione', $ricerca->getTc2TipoProceduraAttivazione());
        $q->setParameter(':flag_aiuti', '%' . $ricerca->getFlagAiuti() . '%');
        $q->setParameter(':descr_procedura_att', '%' . $ricerca->getDescrProceduraAtt() . '%');
        $q->setParameter(':tc3_responsabile_procedura', $ricerca->getTc3ResponsabileProcedura());
        $q->setParameter(':data_avvio_procedura', $ricerca->getDataAvvioProcedura());
        $q->setParameter(':data_fine_procedura', $ricerca->getDataFineProcedura());
        $q->setParameter(':flg_cancellazione', $ricerca->getFlgCancellazione());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneTavole $tavola): bool {
        $configurazione = $tavola->getMonitoraggioConfigurazioneEsportazione();
        $procedura = $configurazione->getProcedura();

        $qb = $this->getQueryEsportazioneProcedura($procedura);
        $expr = $qb->expr();
        $res = $qb
        ->andWhere(
            $expr->gte(
                "coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
                "coalesce(procedura.data_modifica, procedura.data_creazione,'0000-00-00')"
            )
        )
        ->getQuery()
        ->getOneOrNullResult();

        return !\is_null($res);
    }
}
