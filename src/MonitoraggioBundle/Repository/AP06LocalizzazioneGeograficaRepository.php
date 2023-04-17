<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;
use MonitoraggioBundle\Form\Entity\Strutture\AP06;

class AP06LocalizzazioneGeograficaRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(AP06 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:AP06LocalizzazioneGeografica e '
                . 'join e.localizzazioneGeografica localizzazioneGeografica '
                . 'where e.cod_locale_progetto like :cod_locale_progetto '
                . 'and localizzazioneGeografica.descrizione_comune like :localizzazioneGeografica '
                . "and coalesce(e.indirizzo,'') like :indirizzo "
                . "and coalesce(e.cod_cap,'') like :cod_cap "
                . 'order by e.id asc';
        $q->setDQL($query);
        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':localizzazioneGeografica', '%' . $ricerca->getComune() . '%');
        $q->setParameter(':indirizzo', '%' . $ricerca->getIndirizzo() . '%');
        $q->setParameter(':cod_cap', '%' . $ricerca->getCodCap() . '%');

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();

        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_localizzazione_geografica', 'localizzazione')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(localizzazione.data_modifica, localizzazione.data_creazione,'0000-00-00')")
        )
        ->getQuery()
        ->getSingleResult();
        $dataEsportazione = \array_pop($res);

        return \is_null($dataEsportazione);
    }

    public function findAllEsportabili($date) {
        $query = 'select distinct r '
        . 'from RichiesteBundle:Richiesta r '
        . 'join r.istruttoria istruttoria '
        . 'join r.attuazione_controllo attuazione_controllo '
        . 'join r.proponenti proponenti with proponenti.mandatario = 1 '
        . 'join proponenti.soggetto soggetto '
        . 'join soggetto.sedi sedi '

        . 'where (coalesce(r.data_modifica, r.data_creazione) > :data_esportazione '
        . 'or coalesce(attuazione_controllo.data_modifica, attuazione_controllo.data_creazione) > :data_esportazione '
        . 'or (coalesce(istruttoria.data_modifica, istruttoria.data_creazione) > :data_esportazione) '
        . 'or (coalesce(proponenti.data_modifica, proponenti.data_creazione) > :data_esportazione) '
        . 'or (coalesce(soggetto.data_modifica, soggetto.data_creazione) > :data_esportazione) '
        . 'or (coalesce(sedi.data_modifica, sedi.data_creazione) > :data_esportazione)) AND COALESCE(r.flag_por, 0) = 1 AND COALESCE(r.flag_inviato_monit, 0) = 0';

        return $this->getEntityManager()
                ->createQuery($query)
                ->setParameter('data_esportazione', $date)
                ->iterate();
    }
}
