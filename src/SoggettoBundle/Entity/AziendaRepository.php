<?php

namespace SoggettoBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use SoggettoBundle\Form\Entity\RicercaAzienda;
use RichiesteBundle\Form\Bando101\RicercaSoggettoProponente as Ricerca101;
use RichiesteBundle\GestoriRichieste\GestoreRichiesteBando_97;

class AziendaRepository extends EntityRepository {
    /**
     * Metodo che a partire da una persona con annesso incarico e un determinato contesto rende la lista dei soggetti
     * per cui puo operare
     * @return Query
     */
    public function cercaDaPersonaIncarico(RicercaAzienda $ricercaSoggetto): Query {
        $dql = "SELECT a FROM SoggettoBundle:Azienda a
              JOIN a.incarichi_persone ip
              JOIN ip.incaricato p
              JOIN ip.stato si
              WHERE si.codice = :codiceAttivo ";

        if (!\is_null($ricercaSoggetto->getIncarico())) {
            $dql .= "AND ip.tipoIncarico = :incarico";
        }

        $q = $this->getEntityManager()->createQuery();

        $q->setParameter("codiceAttivo", "ATTIVO");

        if (!\is_null($ricercaSoggetto->getPersonaId())) {
            $dql .= " AND p.id=:personaId ";
            $q->setParameter("personaId", $ricercaSoggetto->getPersonaId());
        }

        if (!\is_null($ricercaSoggetto->getIncarico())) {
            $q->setParameter("incarico", $ricercaSoggetto->getIncarico());
        }

        if ("" != $ricercaSoggetto->getDenominazione()) {
            $dql .= " AND a.denominazione LIKE :denominazione";
            $q->setParameter("denominazione", "%" . $ricercaSoggetto->getDenominazione() . "%");
        }
        if ("" != $ricercaSoggetto->getCodiceFiscale()) {
            $dql .= " AND a.codice_fiscale = :codice_fiscale";
            $q->setParameter("codice_fiscale", $ricercaSoggetto->getCodiceFiscale());
        }
        if ("" != $ricercaSoggetto->getPartitaIva()) {
            $dql .= " AND a.partita_iva = :partita_iva";
            $q->setParameter("partita_iva", $ricercaSoggetto->getPartitaIva());
        }
        if (!\is_null($ricercaSoggetto->getDataCostituzioneA())) {
            $dql .= " AND a.data_costituzione <= :data_costituzione_a";
            $q->setParameter("data_costituzione_a", $ricercaSoggetto->getDataCostituzioneA());
        }
        if (!\is_null($ricercaSoggetto->getDataCostituzioneDa())) {
            $dql .= " AND a.data_costituzione >= :data_costituzione_da";
            $q->setParameter("data_costituzione_da", $ricercaSoggetto->getDataCostituzioneDa());
        }

        $q->setDQL($dql);

        return $q;
    }

    public function searchAzienda($query) {
        $dql = "SELECT azienda.id, azienda.denominazione as text
        FROM SoggettoBundle:Azienda azienda
        WHERE azienda.denominazione like :query
        ";
        return $this->getEntityManager()
        ->createQuery($dql)
        ->setParameter('query', "%$query%")
        ->getResult();
    }

    public function cercaPerProponenteSisma(Ricerca101 $ricerca): Query {
        $qb = $this->createQueryBuilder('s');
        $expr = $qb->expr();
        $codiceFiscale = $ricerca->getCodiceFiscale();

        $qb
        ->innerJoin('s.comune', 'comune')
        ->leftJoin('s.proponenti', 'proponenti')
        ->where(
            $expr->like('s.denominazione', ':denominazione'),
            $expr->eq('s.codice_fiscale', 'COALESCE(:codice_fiscale,s.codice_fiscale)'),
            $expr->eq('s.partita_iva', 'COALESCE(:partita_iva,s.partita_iva)'),
            $expr->in('comune.codice_completo', ':comuni')
        )
        ->setParameters([
            'denominazione' => '%' . $ricerca->getDenominazione() . '%',
            'codice_fiscale' => $ricerca->getCodiceFiscale(),
            'partita_iva' => $ricerca->getPartitaIva(),
            'comuni' => GestoreRichiesteBando_97::AMMISSIBILI,
        ]);

        return $qb->getQuery();
    }
}
