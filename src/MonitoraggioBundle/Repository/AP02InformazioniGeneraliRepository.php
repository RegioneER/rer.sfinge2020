<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

/**
 * Description of TC10TipoLocalizzazioneRepository.
 *
 * @author lfontana
 */
class AP02InformazioniGeneraliRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\AP02 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:AP02InformazioniGenerali e '
                . 'left join e.tc7_progetto_complesso tc7_progetto_complesso '
                . 'left join e.tc8_grande_progetto tc8_grande_progetto '
                . 'left join e.tc9_tipo_livello_istituzione tc9_tipo_livello_istituzione '
                . 'join e.tc10_tipo_localizzazione tc10_tipo_localizzazione '
                . 'join e.tc13_gruppo_vulnerabile_progetto tc13_gruppo_vulnerabile_progetto '
                . 'where (tc7_progetto_complesso = coalesce(:tc7_progetto_complesso, tc7_progetto_complesso) or (:tc7_progetto_complesso is null and tc7_progetto_complesso.id is null)) '
                . 'and (tc8_grande_progetto = coalesce(:tc8_grande_progetto, tc8_grande_progetto) or (tc8_grande_progetto.id is null and :tc8_grande_progetto is null)) '
                . 'and (tc9_tipo_livello_istituzione = coalesce(:tc9_tipo_livello_istituzione, tc9_tipo_livello_istituzione) or (tc9_tipo_livello_istituzione.id is null and :tc9_tipo_livello_istituzione is null) ) '
                . 'and tc10_tipo_localizzazione = coalesce(:tc10_tipo_localizzazione, tc10_tipo_localizzazione) '
                . 'and tc13_gruppo_vulnerabile_progetto = coalesce(:tc13_gruppo_vulnerabile_progetto, tc13_gruppo_vulnerabile_progetto) '
                . 'and e.cod_locale_progetto like :cod_locale_progetto '
                . "and coalesce(e.generatore_entrate,'') like :generatore_entrate "
                . "and coalesce(e.fondo_di_fondi,'') like :fondo_di_fondi "
                . "and coalesce(e.flg_cancellazione, '') like :flg_cancellazione "
                . 'order by e.id asc';
        $q->setDQL($query);
        $q->setParameter('tc7_progetto_complesso', $ricerca->getTc7ProgettoComplesso());
        $q->setParameter('tc8_grande_progetto', $ricerca->getTc8GrandeProgetto());
        $q->setParameter(':tc9_tipo_livello_istituzione', $ricerca->getTc9TipoLivelloIstituzione());
        $q->setParameter(':tc10_tipo_localizzazione', $ricerca->getTc10TipoLocalizzazione());
        $q->setParameter(':tc13_gruppo_vulnerabile_progetto', $ricerca->getTc13GruppoVulnerabileProgetto());
        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':generatore_entrate', '%' . $ricerca->getGeneratoreEntrate() . '%');
        $q->setParameter(':fondo_di_fondi', '%' . $ricerca->getFondoDiFondi() . '%');
        $q->setParameter(':flg_cancellazione', '%' . $ricerca->getFlgCancellazione() . '%');

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura): bool {
        $richiesta = $struttura->getRichiesta();

        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $qb->andWhere(
                $expr->gte("coalesce(richiesta.data_modifica, richiesta.data_creazione, '0000-00-00')"
                , "coalesce(struttura.data_modifica, struttura.data_creazione, '0000-00-00')"),
                $expr->orX(
                        $expr->isNotNull('richiesta.mon_progetto_complesso'),
                        $expr->isNotNull('richiesta.mon_grande_progetto'),
                        $expr->isNotNull('richiesta.mon_liv_istituzione_str_fin')
                ),
                $expr->isNotNull('richiesta.mon_gruppo_vulnerabile')
        );
        $result = $qb->getQuery()->getSingleResult();
        $maxDataesportazionePregressa = \array_pop($result);

        return \is_null($maxDataesportazionePregressa);
    }

    public function findAllEsportabili($date) {
        $query = 'select distinct richiesta '
        . 'from RichiesteBundle:Richiesta richiesta '
        . 'join richiesta.istruttoria istruttoria '
        . 'join richiesta.attuazione_controllo attuazione_controllo '
        . 'join richiesta.mon_progetto_complesso mon_progetto_complesso '
        . 'join richiesta.mon_grande_progetto mon_grande_progetto '
        . 'join richiesta.mon_liv_istituzione_str_fin mon_liv_istituzione_str_fin '
        . 'join richiesta.mon_tipo_localizzazione mon_tipo_localizzazione '
        . 'join richiesta.mon_gruppo_vulnerabile mon_gruppo_vulnerabile '
        . 'where (coalesce(richiesta.data_modifica, richiesta.data_creazione) > :data_esportazione '
                . 'or coalesce(attuazione_controllo.data_modifica, attuazione_controllo.data_creazione) > :data_esportazione '
                . 'or coalesce(mon_progetto_complesso.data_modifica, mon_progetto_complesso.data_creazione) > :data_esportazione '
                . 'or coalesce(mon_grande_progetto.data_modifica, mon_grande_progetto.data_creazione) > :data_esportazione '
                . 'or coalesce(mon_liv_istituzione_str_fin.data_modifica, mon_liv_istituzione_str_fin.data_creazione) > :data_esportazione '
                . 'or coalesce(mon_tipo_localizzazione.data_modifica, mon_tipo_localizzazione.data_creazione) > :data_esportazione '
                . 'or coalesce(mon_gruppo_vulnerabile.data_modifica, mon_gruppo_vulnerabile.data_creazione) > :data_esportazione '
                . 'or (coalesce(istruttoria.data_modifica, istruttoria.data_creazione) > :data_esportazione)) AND COALESCE(richiesta.flag_por, 0) = 1 AND COALESCE(richiesta.flag_inviato_monit, 0) = 0';

        return $this->getEntityManager()
                ->createQuery($query)
                ->setParameter('data_esportazione', $date)
                ->iterate();
    }
}
