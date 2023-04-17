<?php

namespace SoggettoBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use SoggettoBundle\Form\Entity\RicercaAzienda;
use SoggettoBundle\Form\Entity\RicercaComuneUnione;

/**
 * ComuneUnioneRepository
 *
 */
class ComuneUnioneRepository extends EntityRepository {


    /**
     * Metodo che a partire da una persona con annesso incarico e un determinato contesto rende la lista dei soggetti
     * per cui puo operare
     * @return Query
     */
    public function cercaDaPersonaIncarico(RicercaComuneUnione $ricercaSoggetto)
    {
        $dql = "SELECT a FROM SoggettoBundle:ComuneUnione a
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

        if ($ricercaSoggetto->getDenominazione() != "") {
            $dql .= " AND a.denominazione LIKE :denominazione";
            $q->setParameter("denominazione", "%" . $ricercaSoggetto->getDenominazione() . "%");
        }
        if ($ricercaSoggetto->getCodiceFiscale() != "") {
            $dql .= " AND a.codice_fiscale = :codice_fiscale";
            $q->setParameter("codice_fiscale", $ricercaSoggetto->getCodiceFiscale());
        }
        if ($ricercaSoggetto->getPartitaIva() != "") {
            $dql .= " AND a.partita_iva = :partita_iva";
            $q->setParameter("partita_iva", $ricercaSoggetto->getPartitaIva());
        }

        $q->setDQL($dql);

        return $q;

    }

}
