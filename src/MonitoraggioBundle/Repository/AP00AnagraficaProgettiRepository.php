<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Form\Entity\Strutture\AP00;
use MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase;

class AP00AnagraficaProgettiRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(AP00 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:AP00AnagraficaProgetti e '
                . 'join e.tc5_tipo_operazione tc5_tipo_operazione '
                . 'join e.tc6_tipo_aiuto tc6_tipo_aiuto '
                . 'join e.tc48_tipo_procedura_attivazione_originaria tc48_tipo_procedura_attivazione_originaria '
                . 'where tc5_tipo_operazione = coalesce(:tc5_tipo_operazione, tc5_tipo_operazione) '
                    . 'and tc6_tipo_aiuto = coalesce(:tc6_tipo_aiuto, tc6_tipo_aiuto) '
                    . 'and tc48_tipo_procedura_attivazione_originaria = coalesce(:tc48_tipo_procedura_attivazione_originaria, tc48_tipo_procedura_attivazione_originaria) '
                    . 'and e.cod_locale_progetto like :cod_locale_progetto '
                    . 'and e.titolo_progetto like :titolo_progetto '
                    . 'and e.sintesi_prg like :sintesi_prg '
                    . 'and e.cup like :cup '
                    . 'and e.data_inizio = coalesce(:data_inizio, e.data_inizio) '
                    . 'and e.data_fine_prevista = coalesce(:data_fine_prevista, e.data_fine_prevista) '
                    . "and coalesce( e.data_fine_effettiva, '9999-12-31') = coalesce(:data_fine_effettiva, e.data_fine_effettiva, '9999-12-31') "
                    . 'and e.codice_proc_att_orig like :codice_proc_att_orig '
                    . "and coalesce(e.flg_cancellazione, '') like :flg_cancellazione "
                . 'order by e.id asc';
        $q->setDQL($query);
        $q->setParameter('tc5_tipo_operazione', $ricerca->getTc5TipoOperazione());
        $q->setParameter('tc6_tipo_aiuto', $ricerca->getTc6TipoAiuto());
        $q->setParameter(':tc48_tipo_procedura_attivazione_originaria', $ricerca->getTc48TipoProceduraAttivazioneOriginaria());
        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':titolo_progetto', '%' . $ricerca->getTitoloProgetto() . '%');
        $q->setParameter(':sintesi_prg', '%' . $ricerca->getSintesiPrg() . '%');
        $q->setParameter(':cup', '%' . $ricerca->getCup() . '%');
        $q->setParameter(':data_inizio', $ricerca->getDataInizio());
        $q->setParameter(':data_fine_prevista', $ricerca->getDataFinePrevista());
        $q->setParameter(':data_fine_effettiva', $ricerca->getDataFineEffettiva());
        $q->setParameter(':codice_proc_att_orig', '%' . $ricerca->getCodiceProcAttOrig() . '%');
        $q->setParameter(':flg_cancellazione', '%' . $ricerca->getFlgCancellazione() . '%');

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura):bool {
        $richiesta = $struttura->getRichiesta();

        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $qb->join('richiesta.richieste_protocollo','protocollo')
        ->andWhere(
            $expr->orX(
                $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
                "coalesce(protocollo.data_modifica, protocollo.data_creazione,'0000-00-00')"),
                $expr->gte("coalesce(richiesta.data_modifica, richiesta.data_creazione, '0000-00-00')"
                , "coalesce(struttura.data_modifica, struttura.data_creazione, '0000-00-00')")
            )
        );
        $result = $qb->getQuery()->getSingleResult();
        $maxDataesportazionePregressa = \array_pop($result);

        return \is_null($maxDataesportazionePregressa);
    }

    public function findAllEsportabili($date) {
        $query = "SELECT distinct r 
            from RichiesteBundle:Richiesta r 
            join r.istruttoria istruttoria 
            join r.attuazione_controllo attuazione_controllo 
            where (
                coalesce(r.data_modifica, r.data_creazione) > :data_esportazione 
                or coalesce(attuazione_controllo.data_modifica, attuazione_controllo.data_creazione) > :data_esportazione 
                or coalesce(istruttoria.data_modifica, istruttoria.data_creazione) > :data_esportazione
            )
            AND COALESCE(r.flag_por, 0) = 1 AND COALESCE(r.flag_inviato_monit, 0) = 0 ";

        return $this->getEntityManager()
        ->createQuery($query)
        ->setParameter('data_esportazione', $date)
        ->iterate();
    }
}
