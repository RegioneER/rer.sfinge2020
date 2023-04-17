<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

class FN00FinanziamentoRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\FN00 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:FN00Finanziamento e '
                . 'join e.tc16_localizzazione_geografica localizzazioneGeografica '
                . 'join e.tc33_fonte_finanziaria tc33_fonte_finanziaria '
                . 'join e.tc35_norma tc35_norma '
                . 'join e.tc34_delibera_cipe tc34_delibera_cipe '
                . 'where e.cod_locale_progetto like :cod_locale_progetto '
                . 'and localizzazioneGeografica.descrizione_comune like :localizzazioneGeografica '
                . 'and e.cf_cofinanz like :cf_cofinanz '
                . 'and tc33_fonte_finanziaria = coalesce( :tc33_fonte_finanziaria, tc33_fonte_finanziaria) '
                . 'and tc35_norma = coalesce( :tc35_norma, tc35_norma) '
                . 'and tc34_delibera_cipe = coalesce( :tc34_delibera_cipe, tc34_delibera_cipe) '
                . 'and coalesce( e.importo, 0) = coalesce( :importo, e.importo, 0) '
                . 'order by e.id asc';
        $q->setDQL($query);
        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':localizzazioneGeografica', $ricerca->getComune() . '%');
        $q->setParameter(':tc33_fonte_finanziaria', $ricerca->getTc33FonteFinanziaria());
        $q->setParameter(':tc35_norma', $ricerca->getTc35Norma());
        $q->setParameter(':tc34_delibera_cipe', $ricerca->getTc34DeliberaCipe());

        $q->setParameter(':cf_cofinanz', '%' . $ricerca->getCfCofinanz() . '%');
        $q->setParameter(':importo', $ricerca->getImporto());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();

        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_finanziamenti', 'finanziamenti')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(finanziamenti.data_modifica, finanziamenti.data_creazione,'0000-00-00')")
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
        . 'join richiesta.mon_finanziamenti mon_finanziamenti '
        . 'join mon_finanziamenti.cofinanziatore cofinanziatore '

        . 'where (coalesce(richiesta.data_modifica, richiesta.data_creazione) > :data_esportazione '
                . 'or coalesce(attuazione_controllo.data_modifica, attuazione_controllo.data_creazione) > :data_esportazione '
                . 'or coalesce(mon_finanziamenti.data_modifica, mon_finanziamenti.data_creazione) > :data_esportazione '
                . 'or coalesce(cofinanziatore.data_modifica, cofinanziatore.data_creazione) > :data_esportazione) AND COALESCE(richiesta.flag_por, 0) = 1 AND COALESCE(richiesta.flag_inviato_monit, 0) = 0';

        return $this->getEntityManager()
                ->createQuery($query)
                ->setParameter('data_esportazione', $date)
                ->iterate();
    }
}
