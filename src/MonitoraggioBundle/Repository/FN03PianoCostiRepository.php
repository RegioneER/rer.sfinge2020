<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

class FN03PianoCostiRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\FN03 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:FN03PianoCosti e '
                . "where e.cod_locale_progetto like :cod_locale_progetto "
                . "and coalesce( e.anno_piano, 0) = coalesce( :anno_piano, e.anno_piano, 0) "
                . "and coalesce( e.imp_realizzato, 0) = coalesce( :imp_realizzato, e.imp_realizzato, 0) "
                . "and coalesce( e.imp_da_realizzare, 0) = coalesce( :imp_da_realizzare, e.imp_da_realizzare, 0) "
                . "order by e.id asc";
        $q->setDQL($query);

        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':anno_piano', $ricerca->getAnnoPiano());
        $q->setParameter(':imp_realizzato', $ricerca->getImpRealizzato());
        $q->setParameter(':imp_da_realizzare', $ricerca->getImpDaRealizzare());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();
        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_piano_costi', 'mon_piano_costi')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(mon_piano_costi.data_modifica, mon_piano_costi.data_creazione,'0000-00-00')")
        )
        ->getQuery()
        ->getSingleResult();
        $dataEsportazione = \array_pop($res);

        return \is_null($dataEsportazione);
    }

    public function findAllEsportabili($date) {
        $query = "SELECT distinct r 
                from RichiesteBundle:Richiesta r 
                inner join r.attuazione_controllo attuazione_controllo 
                inner join r.voci_piano_costo voci_piano_costo 
                inner join voci_piano_costo.istruttoria istruttoria
                left join attuazione_controllo.pagamenti pagamenti
                where (
                    coalesce(r.data_modifica, r.data_creazione) > :data_esportazione 
                    or coalesce(voci_piano_costo.data_modifica, voci_piano_costo.data_creazione) > :data_esportazione 
                    or coalesce(attuazione_controllo.data_modifica, attuazione_controllo.data_creazione) > :data_esportazione 
                    or coalesce(istruttoria.data_modifica, istruttoria.data_creazione) > :data_esportazione
                    or coalesce(pagamenti.data_modifica, pagamenti.data_creazione) > :data_esportazione
                ) 
                AND COALESCE(r.flag_por, 0) = 1 AND COALESCE(r.flag_inviato_monit, 0) = 0
        ";
        return $this->getEntityManager()
                        ->createQuery($query)
                        ->setParameter('data_esportazione', $date)
                        ->iterate();
    }
}
