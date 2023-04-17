<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

class FN06PagamentiRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\FN06 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:FN06Pagamenti e '
                . 'join e.tc39_causale_pagamento tc39_causale_pagamento '
                . "where e.cod_locale_progetto like :cod_locale_progetto "
                . "and coalesce(e.cod_pagamento,'') like :cod_pagamento "
                . "and coalesce(e.tipologia_pag,'') = coalesce(:tipologia_pag, e.tipologia_pag, '')  "
                . "and tc39_causale_pagamento = coalesce( :tc39_causale_pagamento, tc39_causale_pagamento) "
                . "and coalesce( e.data_pagamento, '9999-12-31') = coalesce( :data_pagamento, e.data_pagamento, '9999-12-31') "
                . "and coalesce( e.note_pag, '') like :note_pag "
                . "and coalesce( e.importo_pag, 0) = coalesce( :importo_pag, e.importo_pag, 0) "
                . "order by e.id asc";
        $q->setDQL($query);

        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':cod_pagamento', '%' . $ricerca->getCodPagamento() . '%');
        $q->setParameter(':tipologia_pag', $ricerca->getTipologiaPag());
        $q->setParameter(':tc39_causale_pagamento', $ricerca->getTc39CausalePagamento());
        $q->setParameter(':data_pagamento', $ricerca->getDataPagamento());
        $q->setParameter(':importo_pag', $ricerca->getImportoPag());
        $q->setParameter(':note_pag', '%' . $ricerca->getNotePag() . '%');

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();
        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_richieste_pagamento', 'pagamenti')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(pagamenti.data_modifica, pagamenti.data_creazione,'0000-00-00')")
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
                . "where (coalesce(r.data_modifica, r.data_creazione) > :data_esportazione "
                . "or coalesce(mon_richieste_pagamento.data_modifica, mon_richieste_pagamento.data_creazione) > :data_esportazione) 
                AND COALESCE(r.flag_por, 0) = 1 AND COALESCE(r.flag_inviato_monit, 0) = 0";
        return $this->getEntityManager()
                        ->createQuery($query)
                        ->setParameter('data_esportazione', $date)
                        ->iterate();
    }
}
