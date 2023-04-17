<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

class AP04ProgrammaRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\AP04 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
            . 'from MonitoraggioBundle:AP04Programma e '
                . 'join e.tc4_programma tc4_programma '
                . 'left join e.tc14_specifica_stato tc14_specifica_stato '
            . 'where e.cod_locale_progetto like :cod_locale_progetto '
                 . 'and (tc14_specifica_stato = COALESCE(:tc14_specifica_stato, tc14_specifica_stato) or tc14_specifica_stato.id is null )'
                 . 'and tc4_programma = COALESCE(:tc4_programma, tc4_programma) '
                    . 'and e.stato = coalesce(:stato,e.stato) '
           . 'order by e.id asc';
        $q->setDQL($query);
        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':stato', $ricerca->getStato());
        $q->setParameter(':tc14_specifica_stato', $ricerca->getTc14SpecificaStato());
        $q->setParameter(':tc4_programma', $ricerca->getTc4Programma());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();

        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_programmi', 'programmi')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(programmi.data_modifica, programmi.data_creazione,'0000-00-00')")
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
        . 'join r.mon_programmi mon_programmi '
        . 'where (coalesce(r.data_modifica, r.data_creazione) > :data_esportazione '
        . 'or coalesce(attuazione_controllo.data_modifica, attuazione_controllo.data_creazione) > :data_esportazione '
        . 'or (coalesce(istruttoria.data_modifica, istruttoria.data_creazione) > :data_esportazione) '
        . 'or (coalesce(mon_programmi.data_modifica, mon_programmi.data_creazione) > :data_esportazione)) AND COALESCE(r.flag_por, 0) = 1 AND COALESCE(r.flag_inviato_monit, 0) = 0';

        return $this->getEntityManager()
                ->createQuery($query)
                ->setParameter('data_esportazione', $date)
                ->iterate();
    }
}
