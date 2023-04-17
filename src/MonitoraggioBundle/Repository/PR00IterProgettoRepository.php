<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

/**
 * @author gorlando
 */
class PR00IterProgettoRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\PR00 $ricerca) {
        $q = $this->getEntityManager()->createQuery();

        $query = "
			SELECT e 
			FROM MonitoraggioBundle:PR00IterProgetto e 
			JOIN e.tc46_fase_procedurale tc46_fase_procedurale 
			WHERE 
			e.cod_locale_progetto LIKE :cod_locale_progetto 
			AND coalesce(e.data_inizio_prevista,'9999-12-31') = coalesce(:data_inizio_prevista, e.data_inizio_prevista, '9999-12-31') 
			AND coalesce(e.data_inizio_effettiva,'9999-12-31') = coalesce(:data_inizio_effettiva, e.data_inizio_effettiva, '9999-12-31') 
			AND coalesce(e.data_fine_prevista,'9999-12-31') = coalesce(:data_fine_prevista, e.data_fine_prevista, '9999-12-31') 
			AND coalesce(e.data_fine_effettiva,'9999-12-31') = coalesce(:data_fine_effettiva, e.data_fine_effettiva, '9999-12-31') 
			";

        $q->setDQL($query);

        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':data_inizio_prevista', $ricerca->getDataInizioPrevista());
        $q->setParameter(':data_inizio_effettiva', $ricerca->getDataInizioEffettiva());
        $q->setParameter(':data_fine_prevista', $ricerca->getDataFinePrevista());
        $q->setParameter(':data_fine_effettiva', $ricerca->getDataFineEffettiva());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();
        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.voci_fase_procedurale', 'fasi_procedurali')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(fasi_procedurali.data_modifica, fasi_procedurali.data_creazione,'0000-00-00')")
        )
        ->getQuery()
        ->getSingleResult();
        $dataEsportazione = \array_pop($res);

        return \is_null($dataEsportazione);
    }

    public function findAllEsportabili($date) {
        $query = 'select distinct r '
                . 'from RichiesteBundle:Richiesta r '
                . 'join r.attuazione_controllo attuazione_controllo '
                . 'join r.mon_stato_progetti mon_stato_progetti '
                . "where (coalesce(r.data_modifica, r.data_creazione) > :data_esportazione "
                . "or coalesce(mon_stato_progetti.data_modifica, mon_stato_progetti.data_creazione) > :data_esportazione)
                AND COALESCE(r.flag_por, 0) = 1 AND COALESCE(r.flag_inviato_monit, 0) = 0 ";
        return $this->getEntityManager()
                        ->createQuery($query)
                        ->setParameter('data_esportazione', $date)
                        ->iterate();
    }
}
