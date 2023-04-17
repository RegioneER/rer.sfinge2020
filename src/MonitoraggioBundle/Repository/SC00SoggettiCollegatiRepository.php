<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

class SC00SoggettiCollegatiRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\SC00 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:SC00SoggettiCollegati e '
                . 'join e.tc24_ruolo_soggetto tc24_ruolo_soggetto '
                . 'join e.tc25_forma_giuridica tc25_forma_giuridica '
                . 'join e.tc26_ateco tc26_ateco '
                . 'where e.cod_locale_progetto like :cod_locale_progetto '
                . 'and tc24_ruolo_soggetto = COALESCE(:tc24_ruolo_soggetto, tc24_ruolo_soggetto) '
                . 'and tc25_forma_giuridica = COALESCE(:tc25_forma_giuridica, tc25_forma_giuridica) '
                . 'and tc26_ateco = COALESCE(:tc26_ateco, tc26_ateco) '
                . "and coalesce(e.codice_fiscale,'') like :codice_fiscale "
                . "and coalesce(e.cod_uni_ipa,'') like :cod_uni_ipa "
                . "and coalesce(e.flag_soggetto_pubblico,'') like :flag_soggetto_pubblico "
                . "and coalesce(e.denominazione_sog,'') like :denominazione_sog "
                . "and coalesce(e.note,'') like :note "
                . 'order by e.id asc';
        $q->setDQL($query);
        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':codice_fiscale', '%' . $ricerca->getCodiceFiscale() . '%');
        $q->setParameter(':flag_soggetto_pubblico', '%' . $ricerca->getFlagSoggettoPubblico() . '%');
        $q->setParameter(':cod_uni_ipa', '%' . $ricerca->getCodUniIpa() . '%');
        $q->setParameter(':denominazione_sog', '%' . $ricerca->getDenominazioneSog() . '%');
        $q->setParameter(':note', '%' . $ricerca->getNote() . '%');

        $q->setParameter(':tc24_ruolo_soggetto', $ricerca->getTc24RuoloSoggetto());
        $q->setParameter(':tc25_forma_giuridica', $ricerca->getTc25FormaGiuridica());
        $q->setParameter(':tc26_ateco', $ricerca->getTc26Ateco());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();
        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_soggetti_correlati', 'soggettiCollegati')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(soggettiCollegati.data_modifica, soggettiCollegati.data_creazione,'0000-00-00')")
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
        . 'join richiesta.mon_soggetti_correlati mon_soggetti_correlati '
        . 'where (coalesce(richiesta.data_modifica, richiesta.data_creazione) > :data_esportazione '
                . 'or coalesce(attuazione_controllo.data_modifica, attuazione_controllo.data_creazione) > :data_esportazione '
                . 'or coalesce(mon_soggetti_correlati.data_modifica, mon_soggetti_correlati.data_creazione) > :data_esportazione '
                . 'or coalesce(istruttoria.data_modifica, istruttoria.data_creazione) > :data_esportazione) AND COALESCE(richiesta.flag_por, 0) = 1 AND COALESCE(richiesta.flag_inviato_monit, 0) = 0';

        return $this->getEntityManager()
                ->createQuery($query)
                ->setParameter('data_esportazione', $date)
                ->iterate();
    }
}
