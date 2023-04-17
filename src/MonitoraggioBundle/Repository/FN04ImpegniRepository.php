<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

class FN04ImpegniRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\FN04 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:FN04Impegni e '
                . 'left join e.tc38_causale_disimpegno tc38_causale_disimpegno '
                . "where e.cod_locale_progetto like :cod_locale_progetto "
                . "and coalesce(e.cod_impegno,'') like :cod_impegno  "
                . "and coalesce(e.tipologia_impegno,'') like :tipologia_impegno  "
                . "and (tc38_causale_disimpegno = coalesce( :tc38_causale_disimpegno, tc38_causale_disimpegno) or tc38_causale_disimpegno is null) "
                . "and coalesce( e.data_impegno, '9999-12-31') = coalesce( :data_impegno, e.data_impegno, '9999-12-31') "
                . "and coalesce( e.note_impegno, '') like :note_impegno "
                . "and coalesce( e.importo_impegno, 0) = coalesce( :importo_impegno, e.importo_impegno, 0) "
                . "order by e.id asc";
        $q->setDQL($query);

        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':cod_impegno', '%' . $ricerca->getCodImpegno() . '%');
        $q->setParameter(':tipologia_impegno', '%' . $ricerca->getTipologiaImpegno() . '%');
        $q->setParameter(':tc38_causale_disimpegno', $ricerca->getTc38CausaleDisimpegno());
        $q->setParameter(':data_impegno', $ricerca->getDataImpegno());
        $q->setParameter(':importo_impegno', $ricerca->getImportoImpegno());
        $q->setParameter(':note_impegno', '%' . $ricerca->getNoteImpegno() . '%');

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();
        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_impegni', 'impegni')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(impegni.data_modifica, impegni.data_creazione,'0000-00-00')")
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
                . 'join r.mon_impegni mon_impegni '
                . "where (coalesce(r.data_modifica, r.data_creazione) > :data_esportazione "
                . "or coalesce(mon_impegni.data_modifica, mon_impegni.data_creazione) > :data_esportazione) 
                AND COALESCE(r.flag_por, 0) = 1 AND COALESCE(r.flag_inviato_monit, 0) = 0";
        return $this->getEntityManager()
                        ->createQuery($query)
                        ->setParameter('data_esportazione', $date)
                        ->iterate();
    }
}
