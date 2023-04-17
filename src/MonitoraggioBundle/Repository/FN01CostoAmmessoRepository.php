<?php

namespace MonitoraggioBundle\Repository;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;

class FN01CostoAmmessoRepository extends EsportazioneProgettoRepository {
    public function findAllFiltered(\MonitoraggioBundle\Form\Entity\Strutture\FN01 $ricerca) {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:FN01CostoAmmesso e '
                . 'join e.tc4_programma tc4_programma '
                . 'join e.tc36_livello_gerarchico tc36_livello_gerarchico '
                . "where e.cod_locale_progetto like :cod_locale_progetto "
                . "and tc4_programma = coalesce( :tc4_programma, tc4_programma) "
                . "and tc36_livello_gerarchico = coalesce( :tc36_livello_gerarchico, tc36_livello_gerarchico) "
                . "and coalesce( e.importo_ammesso, 0) = coalesce( :importo_ammesso, e.importo_ammesso, 0) "
                . "order by e.id asc";
        $q->setDQL($query);

        $q->setParameter(':cod_locale_progetto', '%' . $ricerca->getCodLocaleProgetto() . '%');
        $q->setParameter(':tc4_programma', $ricerca->getTc4Programma());
        $q->setParameter(':tc36_livello_gerarchico', $ricerca->getTc36LivelloGerarchico());
        $q->setParameter(':importo_ammesso', $ricerca->getImportoAmmesso());

        return $q;
    }

    public function isEsportabile(MonitoraggioConfigurazioneEsportazioneRichiesta $struttura):bool {
        $richiesta = $struttura->getRichiesta();

        $qb = $this->getQueryEsportazioneRichiesta($richiesta);
        $expr = $qb->expr();
        $res = $qb
        ->join('richiesta.mon_programmi', 'programmi')
        ->join('programmi.mon_livelli_gerarchici', 'mon_livelli_gerarchici')
        ->andWhere(
            $expr->gte("coalesce(struttura.data_modifica, struttura.data_creazione,'0000-00-00')",
            "coalesce(mon_livelli_gerarchici.data_modifica, mon_livelli_gerarchici.data_creazione,'0000-00-00')")
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
                inner join r.mon_programmi mon_programmi 
                inner join mon_programmi.mon_livelli_gerarchici mon_livelli_gerarchici
                inner join mon_livelli_gerarchici.tc36_livello_gerarchico tc36
                inner join r.procedura procedura
                inner join procedura.asse asse
                inner join asse.livello_gerarchico liv_asse
                where (coalesce(r.data_modifica, r.data_creazione) > :data_esportazione 
                or coalesce(mon_programmi.data_modifica, mon_programmi.data_creazione) > :data_esportazione 
                or coalesce(attuazione_controllo.data_modifica, attuazione_controllo.data_creazione) > :data_esportazione 
                or coalesce(mon_livelli_gerarchici.data_modifica, mon_livelli_gerarchici.data_creazione) > :data_esportazione) 
                AND COALESCE(r.flag_por, 0) = 1 
                AND COALESCE(r.flag_inviato_monit, 0) = 0
                AND liv_asse <> tc36
        ";
        return $this->getEntityManager()
                        ->createQuery($query)
                        ->setParameter('data_esportazione', $date)
                        ->iterate();
    }
}
