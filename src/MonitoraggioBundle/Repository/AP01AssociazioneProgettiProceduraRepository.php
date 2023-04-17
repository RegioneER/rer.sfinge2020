<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

/**
 * @author afavilli
 */
class AP01AssociazioneProgettiProceduraRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\AP01 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select AP01 '
                . 'from MonitoraggioBundle:AP01AssociazioneProgettiProcedura AP01 '
                . 'join AP01.tc1_procedura_attivazione tc1_procedura_attivazione '
                . 'where AP01.cod_locale_progetto like :cod_locale_progetto '
                . 'and tc1_procedura_attivazione = COALESCE(:tc1_procedura_attivazione, tc1_procedura_attivazione) '
                . 'order by AP01.id asc';
        $q->setDQL($query);
        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':tc1_procedura_attivazione', $ricerca->getTc1ProceduraAttivazione());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();

        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $qb->join('richiesta.procedura', 'procedura')
        ->join('procedura.mon_proc_att', 'attivazione')
        ->andWhere(
            $expr->orX(
                $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
                "coalesce(richiesta.data_modifica, richiesta.data_creazione, '0000-00-00')"),
                $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
                "coalesce(attivazione.data_modifica, attivazione.data_creazione, '0000-00-00')")
            )
        );
        $result = $qb->getQuery()->getSingleResult();
        $maxDataesportazionePregressa = \array_pop($result);

        return \is_null($maxDataesportazionePregressa);
    }

    public function findAllEsportabili($date) {
        $query = 'select distinct r '
        . 'from RichiesteBundle:Richiesta r '
        . 'join r.istruttoria istruttoria '
        . 'join r.attuazione_controllo attuazione_controllo '
        . 'where (coalesce(r.data_modifica, r.data_creazione) > :data_esportazione '
        . 'or coalesce(attuazione_controllo.data_modifica, attuazione_controllo.data_creazione) > :data_esportazione '
        . 'or (coalesce(istruttoria.data_modifica, istruttoria.data_creazione) > :data_esportazione) ) AND COALESCE(r.flag_por, 0) = 1 AND COALESCE(r.flag_inviato_monit, 0) = 0';

        return $this->getEntityManager()
                ->createQuery($query)
                ->setParameter('data_esportazione', $date)
                ->iterate();
    }
}
