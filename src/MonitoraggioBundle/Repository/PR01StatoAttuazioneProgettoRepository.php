<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

/**
 * @author gorlando
 */
class PR01StatoAttuazioneProgettoRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\PR01 $ricerca) {
        $q = $this->getEntityManager()->createQuery();

        $query = "
			SELECT e 
			FROM MonitoraggioBundle:PR01StatoAttuazioneProgetto e 
			JOIN e.tc47_stato_progetto tc47_stato_progetto 
			WHERE 
			e.cod_locale_progetto LIKE :cod_locale_progetto 
			AND tc47_stato_progetto = coalesce(:tc47_stato_progetto, tc47_stato_progetto) 
			AND coalesce(e.data_riferimento,'9999-12-31') = coalesce(:data_riferimento, e.data_riferimento, '9999-12-31') 
			";

        $q->setDQL($query);

        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':tc47_stato_progetto', $ricerca->getTc47StatoProgetto());
        $q->setParameter(':data_riferimento', $ricerca->getDataRiferimento());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();
        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_iter_progetti', 'iter_progetto')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(iter_progetto.data_modifica, iter_progetto.data_creazione,'0000-00-00')")
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
                . 'join r.mon_iter_progetti mon_iter_progetti '
                . "where (coalesce(r.data_modifica, r.data_creazione) > :data_esportazione "
                . "or coalesce(mon_iter_progetti.data_modifica, mon_iter_progetti.data_creazione) > :data_esportazione)
                AND COALESCE(r.flag_por, 0) = 1 AND COALESCE(r.flag_inviato_monit, 0) = 0 ";
        return $this->getEntityManager()
                        ->createQuery($query)
                        ->setParameter('data_esportazione', $date)
                        ->iterate();
    }
}
