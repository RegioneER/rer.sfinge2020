<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTrasferimento;

class TR00TrasferimentiRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\TR00 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TR00Trasferimenti e '
                . 'join e.tc4_programma tc4_programma '
                . 'join e.tc49_causale_trasferimento tc49_causale_trasferimento '
                . "where tc4_programma = coalesce(:tc4_programma, tc4_programma) "
                . "and tc49_causale_trasferimento = coalesce(:tc49_causale_trasferimento, tc49_causale_trasferimento) "
                . "and e.cod_trasferimento like :cod_trasferimento "
                . "and coalesce(e.cf_sog_ricevente,'') like :cf_sog_ricevente "
                . "and e.data_trasferimento = coalesce(:data_trasferimento, e.data_trasferimento) "
                . "and e.importo_trasferimento = coalesce(:importo_trasferimento, e.importo_trasferimento) "
                . "and coalesce(e.flag_soggetto_pubblico, '') = coalesce( :flag_soggetto_pubblico, e.flag_soggetto_pubblico) "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter('tc4_programma', $ricerca->getTc4Programma());
        $q->setParameter('tc49_causale_trasferimento', $ricerca->getTc49CausaleTrasferimento());
        $q->setParameter(':data_trasferimento', $ricerca->getDataTrasferimento());
        $q->setParameter(':cod_trasferimento', '%' . $ricerca->getCodTrasferimento() . '%');
        $q->setParameter(':cf_sog_ricevente', '%' . $ricerca->getCfSogRicevente() . '%');
        $q->setParameter(':flag_soggetto_pubblico', $ricerca->getFlagSoggettoPubblico());
        $q->setParameter(':importo_trasferimento', $ricerca->getImportoTrasferimento());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneTrasferimento $struttura): bool {
        $trasferimento = $struttura->getTrasferimento();

        $query = 'select 1 ctrl '
                . 'from MonitoraggioBundle:Trasferimento trasferimento '
                . 'join trasferimento.programma programma '
                . 'join trasferimento.causale_trasferimento causale_trasferimento '
                . 'join trasferimento.soggetto soggetto '
                . 'where trasferimento = :trasferimento '
                . 'and ('
                . '(coalesce(trasferimento.data_modifica, trasferimento.data_creazione) > :data_esportazione) '
                . ') ';
        $res = $this->getEntityManager()
                ->createQuery($query)
                ->setParameter('trasferimento', $trasferimento)
                ->setParameter('data_esportazione', is_null($maxDataesportazionePregressa['data_creazione_esportazione']) ? '0000-00-00' : $maxDataesportazionePregressa['data_creazione_esportazione'])
                ->setMaxResults(1)
                ->getOneOrNullResult();

        return is_null($res['ctrl']) ? false : true;
    }
}
