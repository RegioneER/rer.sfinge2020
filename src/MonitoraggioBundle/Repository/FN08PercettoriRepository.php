<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

/**
 * @author gorlando
 */
class FN08PercettoriRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\FN08 $ricerca) {
        $q = $this->getEntityManager()->createQuery();

        $query = "SELECT e 
                FROM MonitoraggioBundle:FN08Percettori e 
                JOIN e.tc40_tipo_percettore tc40_tipo_percettore 
                WHERE 
                e.cod_locale_progetto LIKE :cod_locale_progetto 
                AND e.tipologia_pag LIKE :tipologia_pag 
                AND coalesce(e.data_pagamento,'') = coalesce(:data_pagamento, e.data_pagamento, '') 
                AND e.codice_fiscale LIKE :codice_fiscale 
                AND coalesce(e.flag_soggetto_pubblico,'') = coalesce(:flag_soggetto_pubblico, e.flag_soggetto_pubblico, '')
                AND coalesce(e.importo,'') = coalesce(:importo, e.importo, '')
                AND coalesce(e.flg_cancellazione,'') = coalesce(:flg_cancellazione, e.flg_cancellazione, '')
                ";

        $q->setDQL($query);

        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':tipologia_pag', '%' . $ricerca->getTipologiaPag() . '%');
        $q->setParameter(':data_pagamento', $ricerca->getDataPagamento());
        $q->setParameter(':codice_fiscale', '%' . $ricerca->getCodiceFiscale() . '%');
        $q->setParameter(':flag_soggetto_pubblico', $ricerca->getFlagSoggettoPubblico());
        $q->setParameter(':importo', $ricerca->getImporto());
        $q->setParameter(':flg_cancellazione', $ricerca->getFlgCancellazione());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();
        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_richieste_pagamento', 'pagamenti')
        ->join('pagamenti.percettori', 'percettori')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(percettori.data_modifica, percettori.data_creazione,'0000-00-00')")
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
                . 'join r.mon_richieste_pagamento mon_richieste_pagamento '
                . 'join mon_richieste_pagamento.percettori percettori '
                . "where (coalesce(r.data_modifica, r.data_creazione) > :data_esportazione "
                . "or coalesce(mon_richieste_pagamento.data_modifica, mon_richieste_pagamento.data_creazione) > :data_esportazione "
                . "or coalesce(percettori.data_modifica, percettori.data_creazione) > :data_esportazione) AND COALESCE(r.flag_por, 0) = 1 AND COALESCE(r.flag_inviato_monit, 0) = 0";
        return $this->getEntityManager()
                        ->createQuery($query)
                        ->setParameter('data_esportazione', $date)
                        ->iterate();
    }
}
