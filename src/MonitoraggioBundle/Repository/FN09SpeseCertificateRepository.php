<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

class FN09SpeseCertificateRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\FN09 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:FN09SpeseCertificate e '
                . 'join e.tc41_domande_pagamento tc41_domande_pagamento '
                . 'join e.tc36_livello_gerarchico tc36_livello_gerarchico '
                . "where e.cod_locale_progetto like :cod_locale_progetto "
                . "and coalesce(e.tipologia_importo,'') = coalesce(:tipologia_importo, e.tipologia_importo, '')  "
                . "and tc41_domande_pagamento = coalesce( :tc41_domande_pagamento, tc41_domande_pagamento) "
                . "and tc36_livello_gerarchico.descrizione_codice_livello_gerarchico like :tc36_livello_gerarchico "
                . "and coalesce( e.data_domanda, '9999-12-31') = coalesce( :data_domanda, e.data_domanda, '9999-12-31') "
                . "and coalesce( e.importo_spesa_tot, 0) = coalesce( :importo_spesa_tot, e.importo_spesa_tot, 0) "
                . "and coalesce( e.importo_spesa_pub, 0) = coalesce( :importo_spesa_pub, e.importo_spesa_pub, 0) "
                . "order by e.id asc";
        $q->setDQL($query);

        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':tipologia_importo', $ricerca->getTipologiaImporto());
        $q->setParameter(':tc41_domande_pagamento', $ricerca->getTc41DomandePagamento());
        $q->setParameter(':tc36_livello_gerarchico', '%' . $ricerca->getTc36LivelloGerarchico() . '%');
        $q->setParameter(':data_domanda', $ricerca->getDataDomanda());
        $q->setParameter(':importo_spesa_tot', $ricerca->getImportoSpesaTot());
        $q->setParameter(':importo_spesa_pub', $ricerca->getImportoSpesaPub());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();
        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_spese_certificate', 'spese')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(spese.data_modifica, spese.data_creazione,'0000-00-00')")
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
                . 'join r.mon_spese_certificate mon_spese_certificate '
                . "where (coalesce(r.data_modifica, r.data_creazione) > :data_esportazione "
                . "or coalesce(mon_spese_certificate.data_modifica, mon_spese_certificate.data_creazione) > :data_esportazione) 
                AND COALESCE(r.flag_por, 0) = 1 AND COALESCE(r.flag_inviato_monit, 0) = 0";
        return $this->getEntityManager()
                        ->createQuery($query)
                        ->setParameter('data_esportazione', $date)
                        ->iterate();
    }
}
