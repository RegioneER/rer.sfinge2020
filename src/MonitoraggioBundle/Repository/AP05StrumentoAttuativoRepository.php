<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

class AP05StrumentoAttuativoRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\AP05 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:AP05StrumentoAttuativo e '
                . 'join e.tc15_strumento_attuativo tc15_strumento_attuativo '
                . 'where e.cod_locale_progetto like :cod_locale_progetto '
                . 'and tc15_strumento_attuativo = COALESCE(:tc15_strumento_attuativo, tc15_strumento_attuativo) '
                . 'order by e.id asc';
        $q->setDQL($query);
        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':tc15_strumento_attuativo', $ricerca->getTc15StrumentoAttuativo());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();

        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_strumenti_attuativi', 'strumenti')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(strumenti.data_modifica, strumenti.data_creazione,'0000-00-00')")
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
        . 'join r.mon_strumenti_attuativi mon_strumenti_attuativi '
        . 'where (coalesce(r.data_modifica, r.data_creazione) > :data_esportazione '
        . 'or coalesce(attuazione_controllo.data_modifica, attuazione_controllo.data_creazione) > :data_esportazione '
        . 'or (coalesce(istruttoria.data_modifica, istruttoria.data_creazione) > :data_esportazione) '
        . 'or (coalesce(mon_strumenti_attuativi.data_modifica, mon_strumenti_attuativi.data_creazione) > :data_esportazione)) AND COALESCE(r.flag_por, 0) = 1 AND COALESCE(r.flag_inviato_monit, 0) = 0';

        return $this->getEntityManager()
                ->createQuery($query)
                ->setParameter('data_esportazione', $date)
                ->iterate();
    }
}
