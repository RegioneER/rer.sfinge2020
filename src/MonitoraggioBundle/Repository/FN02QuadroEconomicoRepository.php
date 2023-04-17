<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;
use MonitoraggioBundle\Entity\FN02QuadroEconomico;

class FN02QuadroEconomicoRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\FN02 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:FN02QuadroEconomico e '
                . 'join e.tc37_voce_spesa tc37_voce_spesa '
                . "where e.cod_locale_progetto like :cod_locale_progetto "
                . "and tc37_voce_spesa = coalesce( :tc37_voce_spesa, tc37_voce_spesa) "
                . "and coalesce( e.importo, 0) = coalesce( :importo, e.importo, 0) "
                . "order by e.id asc";
        $q->setDQL($query);

        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':tc37_voce_spesa', $ricerca->getTc37VoceSpesa());
        $q->setParameter(':importo', $ricerca->getImporto());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();
        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.voci_piano_costo', 'voci_piano_costo')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(voci_piano_costo.data_modifica, voci_piano_costo.data_creazione,'0000-00-00')")
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
                . 'join r.mon_voce_spesa mon_voce_spesa '
                . "where (coalesce(r.data_modifica, r.data_creazione) > :data_esportazione "
                . "or coalesce(mon_voce_spesa.data_modifica, mon_voce_spesa.data_creazione) > :data_esportazione "
                . "or coalesce(attuazione_controllo.data_modifica, attuazione_controllo.data_creazione) > :data_esportazione) AND COALESCE(r.flag_por, 0) = 1 AND COALESCE(r.flag_inviato_monit, 0) = 0";
        return $this->getEntityManager()
                        ->createQuery($query)
                        ->setParameter('data_esportazione', $date)
                        ->iterate();
    }

    /**
     * @param FN02QuadroEconomico[] $voci
     * @return FN02QuadroEconomico[]
     */
    public function findVociNonPresenti($voci) {
        if (0 == \count($voci)) {
            return [];
        }
        $codiceLocaleProgetto = $voci[0]->getCodLocaleProgetto();
        $tipiVoci = \array_map(function (FN02QuadroEconomico $fn02) {
            return $fn02->getTc37VoceSpesa();
        }, $voci);

        $risultato = $this->createQueryBuilder('fn02')
                ->join('fn02.tc37_voce_spesa', 'tipo_voce')
                ->where(
                        'tipo_voce not in (:tipi_presenti)',
                        'fn02.cod_locale_progetto = :codice_progetto',
                        "fn02.flg_cancellazione is null",
                        "(SELECT count(pregresso) 
                                FROM MonitoraggioBundle:FN02QuadroEconomico pregresso 
                                JOIN pregresso.tc37_voce_spesa tipo_pregresso
                                WHERE pregresso.cod_locale_progetto = fn02.cod_locale_progetto
                                AND tipo_pregresso = tipo_voce
                                AND fn02.data_creazione < pregresso.data_creazione
                                AND pregresso.flg_cancellazione is not null) = 0"
                )
                ->setParameter('tipi_presenti', $tipiVoci)
                ->setParameter('codice_progetto', $codiceLocaleProgetto)
                ->getQuery()
                ->getResult();
        return $risultato;
    }
}
