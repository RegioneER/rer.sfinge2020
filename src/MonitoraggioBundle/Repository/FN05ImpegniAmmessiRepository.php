<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

class FN05ImpegniAmmessiRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\FN05 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:FN05ImpegniAmmessi e '
                . 'join e.tc4_programma tc4_programma '
                . 'join e.tc36_livello_gerarchico tc36_livello_gerarchico '
                . 'left join e.tc38_causale_disimpegno_amm tc38_causale_disimpegno '
                . "where e.cod_locale_progetto like :cod_locale_progetto "
                . "and coalesce(e.cod_impegno,'') like :cod_impegno  "
                . "and coalesce(e.tipologia_impegno,'') = coalesce(:tipologia_impegno, e.tipologia_impegno, '')  "
                . "and coalesce(e.tipologia_imp_amm,'') = coalesce(:tipologia_imp_amm, e.tipologia_imp_amm, '')  "
                . "and tc4_programma = coalesce( :tc4_programma, tc4_programma) "
                . "and tc36_livello_gerarchico = coalesce( :tc36_livello_gerarchico, tc36_livello_gerarchico) "
                . "and coalesce(tc38_causale_disimpegno,0) = coalesce( :tc38_causale_disimpegno, tc38_causale_disimpegno,0) "
                . "and coalesce( e.data_impegno, '9999-12-31') = coalesce( :data_impegno, e.data_impegno, '9999-12-31') "
                . "and coalesce( e.data_imp_amm, '9999-12-31') = coalesce( :data_imp_amm, e.data_imp_amm, '9999-12-31') "
                . "and coalesce( e.note_imp, '') like :note_imp "
                . "and coalesce( e.importo_imp_amm, 0) = coalesce( :importo_imp_amm, e.importo_imp_amm, 0) "
                . "order by e.id asc";
        $q->setDQL($query);

        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':cod_impegno', '%' . $ricerca->getCodImpegno() . '%');
        $q->setParameter(':tipologia_impegno', $ricerca->getTipologiaImpegno());
        $q->setParameter(':tipologia_imp_amm', $ricerca->getTipologiaImpAmm());
        $q->setParameter(':tc4_programma', $ricerca->getTc4Programma());
        $q->setParameter(':tc36_livello_gerarchico', $ricerca->getTc36LivelloGerarchico());
        $q->setParameter(':tc38_causale_disimpegno', $ricerca->getTc38CausaleDisimpegno());
        $q->setParameter(':data_impegno', $ricerca->getDataImpegno());
        $q->setParameter(':data_imp_amm', $ricerca->getDataImpAmm());
        $q->setParameter(':importo_imp_amm', $ricerca->getImportoImpAmm());
        $q->setParameter(':note_imp', '%' . $ricerca->getNoteImp() . '%');

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();
        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_impegni', 'impegni')
        ->join('impegni.mon_impegni_ammessi', 'impegni_ammessi')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(impegni_ammessi.data_modifica, impegni_ammessi.data_creazione,'0000-00-00')")
        )
        ->getQuery()
        ->getSingleResult();
        $dataEsportazione = \array_pop($res);

        return \is_null($dataEsportazione);
    }

    public function findAllEsportabili($date) {
        $query = "SELECT distinct r 
                from RichiesteBundle:Richiesta r 
                join r.attuazione_controllo attuazione_controllo 
                join r.mon_impegni mon_impegni 
                join mon_impegni.mon_impegni_ammessi mon_impegni_ammessi 
                where (
                    coalesce(r.data_modifica, r.data_creazione) > :data_esportazione 
                    or coalesce(mon_impegni.data_modifica, mon_impegni.data_creazione) > :data_esportazione 
                    or coalesce(mon_impegni_ammessi.data_modifica, mon_impegni_ammessi.data_creazione) > :data_esportazione
                ) 
                AND COALESCE(r.flag_por, 0) = 1 
                AND COALESCE(r.flag_inviato_monit, 0) = 0
                ";
        return $this->getEntityManager()
                        ->createQuery($query)
                        ->setParameter('data_esportazione', $date)
                        ->iterate();
    }
}
