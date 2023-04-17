<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 11/07/17
 * Time: 14:13
 */

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;


/**
 * @author afavilli, lfontana
 */
class PA01ProgrammiCollegatiProceduraAttivazioneRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\PA01 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select PA01 '
            . 'from MonitoraggioBundle:PA01ProgrammiCollegatiProceduraAttivazione PA01 '
            . 'join PA01.tc4_programma tc4 '
            . "where coalesce(PA01.cod_proc_att, '') like :cod_proc_att "
            . "and tc4 = coalesce(:tc4_programma,tc4) "
            . "and PA01.importo = coalesce(:importo, PA01.importo) "
            . "and coalesce(PA01.flg_cancellazione,'') = coalesce(:flg_cancellazione, PA01.flg_cancellazione,'') "
            . "order by PA01.id asc";
        $q->setDQL($query);

        $q->setParameter(':cod_proc_att', '%' . $ricerca->getCodProcAtt() . '%');
        $q->setParameter(':tc4_programma', $ricerca->getTc4Programma());
        $q->setParameter(':importo', $ricerca->getImporto());
        $q->setParameter(':flg_cancellazione', $ricerca->getFlgCancellazione());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneTavole $tavola): bool {
        $configurazione = $tavola->getMonitoraggioConfigurazioneEsportazione();
        $procedura = $configurazione->getProcedura();

        $qb = $this->getQueryEsportazioneProcedura($procedura);
        $expr = $qb->expr();
        $res = $qb
        ->join('procedura.mon_procedure_programmi', 'programmi')
        ->andWhere(
            $expr->gte(
                "coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
                "coalesce(programmi.data_modifica, programmi.data_creazione,'0000-00-00')"
            )
        )
        ->getQuery()
        ->getOneOrNullResult();

        return !\is_null($res);
    }
}
