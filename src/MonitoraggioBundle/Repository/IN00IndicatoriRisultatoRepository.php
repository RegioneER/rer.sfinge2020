<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

class IN00IndicatoriRisultatoRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\IN00 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:IN00IndicatoriRisultato e '
                . 'join e.indicatore_id indicatore '
                . 'where e.cod_locale_progetto like :cod_locale_progetto '
                    . 'and e.tipo_indicatore_di_risultato like :tipo_indicatore_di_risultato '
                    . 'and indicatore = coalesce(:cod_indicatore, indicatore) '
                . 'order by e.id asc';
        $q->setDQL($query);
        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':tipo_indicatore_di_risultato', '%' . $ricerca->getTipoIndicatoreDiRisultato() . '%');
        $q->setParameter(':cod_indicatore', $ricerca->getCodIndicatore());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();
        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_indicatore_risultato', 'indicatore_risultato')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(indicatore_risultato.data_modifica, indicatore_risultato.data_creazione,'0000-00-00')")
        )
        ->getQuery()
        ->getSingleResult();
        $dataEsportazione = \array_pop($res);

        return \is_null($dataEsportazione);
    }

    public function findAllEsportabili($date) {
        $query = 'select distinct richiesta '
        . 'from RichiesteBundle:Richiesta richiesta '
        . 'join richiesta.attuazione_controllo attuazione_controllo '
        . 'join richiesta.mon_indicatore_risultato mon_indicatore_risultato '
        . 'where (coalesce(richiesta.data_modifica, richiesta.data_creazione) > :data_esportazione '
                . 'or coalesce(attuazione_controllo.data_modifica, attuazione_controllo.data_creazione) > :data_esportazione '
                . 'or coalesce(mon_indicatore_risultato.data_modifica, mon_indicatore_risultato.data_creazione) > :data_esportazione) AND COALESCE(richiesta.flag_por, 0) = 1 AND COALESCE(richiesta.flag_inviato_monit, 0) = 0 ';

        return $this->getEntityManager()
                ->createQuery($query)
                ->setParameter('data_esportazione', $date)
                ->iterate();
    }
}
