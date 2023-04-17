<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\EntityRepository;
use BaseBundle\Entity\StatoIntegrazione;
use Doctrine\ORM\Query;
use IstruttorieBundle\Form\Entity\RicercaIntegrazione;

class IntegrazioneIstruttoriaRepository extends EntityRepository
{
    static $STATI_INTEGRAZIONE_COMPLETA = array(StatoIntegrazione::INT_INVIATA_PA, StatoIntegrazione::INT_PROTOCOLLATA);

    public function getElencoIntegrazioni(RicercaIntegrazione $ricercaIntegrazione)
    {
        $dql = "SELECT ii "
            . "FROM IstruttorieBundle:IntegrazioneIstruttoria ii "
            . "JOIN ii.istruttoria i "
            . "JOIN i.richiesta rich "
            . "JOIN ii.stato s "
            . "JOIN rich.procedura proc "
            . "LEFT JOIN rich.proponenti prop "
            . "LEFT JOIN ii.richieste_protocollo rp "
            . "LEFT JOIN prop.soggetto_version sv "
            . "WHERE prop.mandatario=1 "
        ;

        $q = $this->getEntityManager()->createQuery();

        if (!is_null($ricercaIntegrazione->getSoggetto())) {
            $dql .= " AND prop.soggetto = :soggetto ";
            $q->setParameter("soggetto", $ricercaIntegrazione->getSoggetto()->getId());
        }

        if (!is_null($ricercaIntegrazione->getProcedura())) {
            $dql .= " AND proc.id = :procedura ";
            $q->setParameter("procedura", $ricercaIntegrazione->getProcedura()->getId());
        }

        $dql .= " AND s.codice IN ('INT_PROTOCOLLATA') ";

        if (!is_null($ricercaIntegrazione->getProtocollo())) {
            $dql .= "AND CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo ";
            $q->setParameter("protocollo", "%".$ricercaIntegrazione->getProtocollo()."%");
        }
        
        if (!is_null($ricercaIntegrazione->getIstruttore())) {
            // Controllo chi ha inviato la comunicazione (lo controllo sul record di protocollazione)
            $dql .= " AND rp.creato_da = :istruttore ";
            $q->setParameter("istruttore", $ricercaIntegrazione->getIstruttore());
        }

        $q->setDQL($dql);

        return $q;
    }

    /**
     * @param RicercaIntegrazione $ricercaIntegrazione
     * @return Query
     */
    public function getElencoIntegrazioniConRispostaNonLetta(RicercaIntegrazione $ricercaIntegrazione)
    {
        $dql = "SELECT ii "
            . "FROM IstruttorieBundle:IntegrazioneIstruttoria ii "
            . "JOIN ii.istruttoria i "
            . "JOIN i.richiesta rich "
            . "JOIN ii.stato s "
            . "JOIN rich.procedura proc "
            . "LEFT JOIN rich.proponenti prop "
            . "LEFT JOIN ii.richieste_protocollo rp "
            . "LEFT JOIN prop.soggetto_version sv "
            . "LEFT JOIN ii.risposta risp "
            . "WHERE prop.mandatario = 1 AND s.codice IN ('INT_PROTOCOLLATA') AND risp.presa_visione = 0 "
        ;

        $q = $this->getEntityManager()->createQuery();
        
        if (!is_null($ricercaIntegrazione->getProcedura())) {
            $dql .= " AND proc.id = :procedura ";
            $q->setParameter("procedura", $ricercaIntegrazione->getProcedura()->getId());
        }

        if (!is_null($ricercaIntegrazione->getIstruttore())) {
            // Controllo chi ha inviato la comunicazione (protocollazione)
            $dql .= " AND rp.creato_da = :istruttore ";
            $q->setParameter("istruttore", $ricercaIntegrazione->getIstruttore());
        }

        $q->setDQL($dql);
        return $q;
    }
}
