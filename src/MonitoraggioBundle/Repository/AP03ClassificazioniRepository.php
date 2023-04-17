<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

/**
 * Description of AP01AssociazioneProgettiProcedura.
 *
 * @author lfontana
 */
class AP03ClassificazioniRepository extends EsportazioneProgettoRepository {
    protected static $ClassiTipologie = [
        'CI' => 'TC121CampoIntervento',
        'FF' => 'TC122FormeFinanziamento',
        'TT' => 'TC123TipoTerritorio',
        'MET' => 'TC124MeccanismiErogazioneTerritoriale',
        'AE' => 'TC125AttivitaEconomiche',
        'DTS' => 'TC126DimensioneTematicaSecondaria',
        'RA' => 'TC127RisultatoAtteso',
        'TI' => 'TC128TipologiaInterventoFeasr',
        'AL' => 'TC129AltreClassificazioni',
        'LA' => 'TC1210LineaAzione',
    ];
    protected static $CociciTipologie = [
        'CI' => 'cod_classificazione_ci',
        'FF' => 'cod_classificazione_fi',
        'TT' => 'cod_classificazione_tt',
        'MET' => 'cod_classificazione_met',
        'AE' => 'cod_classificazione_ae',
        'DTS' => 'cod_classificazione_dts',
        'RA' => 'cod_classificazione_ra',
        'TI' => 'cod_classificazione_ti',
        'AL' => 'cod_classificazione',
        'LA' => 'cod_classificazione_la',
    ];

    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\AP03 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:AP03Classificazioni e '
                . 'join e.tc11_tipo_classificazione tc11_tipo_classificazione '
                . 'join e.tc4_programma tc4_programma '
                . 'join e.classificazione classificazione '
                . 'where e.cod_locale_progetto like :cod_locale_progetto '
                . 'and tc11_tipo_classificazione = COALESCE(:tc11_tipo_classificazione, tc11_tipo_classificazione) '
                . 'and tc4_programma = COALESCE(:tc4_programma, tc4_programma) '
                . 'and classificazione.codice like :cod_classificazione '
                . "and coalesce(e.flg_cancellazione,'') = coalesce(:flg_cancellazione,'') "
                . 'order by e.id asc';
        $q->setDQL($query);
        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':cod_classificazione', '%' . $ricerca->getClassificazione() . '%');
        $q->setParameter(':flg_cancellazione', $ricerca->getFlgCancellazione());
        $q->setParameter(':tc11_tipo_classificazione', $ricerca->getTc11TipoClassificazione());
        $q->setParameter(':tc4_programma', $ricerca->getTc4Programma());

        return $q;
    }

    public function findOneByCategoria(\MonitoraggioBundle\Entity\AP03Classificazioni $ap) {
        $tipoClasse = $ap->getTc11TipoClassificazione()->getTipoClass();
        if (!$tipoClasse) {
            return null;
        }
        $query = 'select e '
                . 'from MonitoraggioBundle:' . self::$ClassiTipologie[$tipoClasse] . ' e '
                . 'where ' . self::$CociciTipologie[$tipoClasse] . ' = :param';
        $q = $this->getEntityManager()->createQuery($query);

        $q->setParameter('param', $ap->getCodClassificazione());

        return $q->getSingleResult();
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();

        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_programmi', 'programmi')
        ->join('programmi.classificazioni', 'classificazioni')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(classificazioni.data_modifica, classificazioni.data_creazione,'0000-00-00')")
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
        . 'join mon_programmi.classificazioni classificazioni '
        . 'where (coalesce(r.data_modifica, r.data_creazione) > :data_esportazione '
        . 'or coalesce(attuazione_controllo.data_modifica, attuazione_controllo.data_creazione) > :data_esportazione '
        . 'or (coalesce(istruttoria.data_modifica, istruttoria.data_creazione) > :data_esportazione) '
        . 'or (coalesce(mon_programmi.data_modifica, mon_programmi.data_creazione) > :data_esportazione) '
        . 'or (coalesce(classificazioni.data_modifica, classificazioni.data_creazione) > :data_esportazione)) AND COALESCE(r.flag_por, 0) = 1 AND COALESCE(r.flag_inviato_monit, 0) = 0';

        return $this->getEntityManager()
                ->createQuery($query)
                ->setParameter('data_esportazione', $date)
                ->iterate();
    }
}
