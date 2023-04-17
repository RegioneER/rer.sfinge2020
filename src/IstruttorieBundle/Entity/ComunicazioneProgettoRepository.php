<?php

namespace IstruttorieBundle\Entity;
use Doctrine\ORM\EntityRepository;
use IstruttorieBundle\Form\Entity\RicercaComunicazionePa;
use Doctrine\ORM\Query;

class ComunicazioneProgettoRepository extends EntityRepository {
		
	public function getElencoComunicazioni( $ricercaComunicazione) {

		$dql = "SELECT ci "
				. "FROM IstruttorieBundle:ComunicazioneProgetto ci "
				. "JOIN ci.richiesta rich "
				. "JOIN ci.stato s "
				. "JOIN rich.procedura proc "
				. "LEFT JOIN rich.proponenti prop "
				. "LEFT JOIN ci.richieste_protocollo rp "
				. "LEFT JOIN prop.soggetto_version sv "			
				. "WHERE prop.mandatario=1 "
				;
		
		$q = $this->getEntityManager()->createQuery();

		if (!is_null($ricercaComunicazione->getSoggetto())) {
			$dql .= " AND prop.soggetto = :soggetto ";
			$q->setParameter("soggetto", $ricercaComunicazione->getSoggetto()->getId());		
		}
		
		if (!is_null($ricercaComunicazione->getProcedura())) {
			$dql .= " AND proc.id = :procedura ";
			$q->setParameter("procedura", $ricercaComunicazione->getProcedura()->getId());		
		}
		
		$dql .= " AND s.codice IN ('COM_PROTOCOLLATA') ";
		
		if (!is_null($ricercaComunicazione->getProtocollo())) {
			$dql .= "AND CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo ";
			$q->setParameter("protocollo", "%".$ricercaComunicazione->getProtocollo()."%");	
		}
		
		$q->setDQL($dql);

		$sql = $q->getSQL();
		return $q;
	}	
	
	public function getElencoComunicazioniVariazioni( $ricercaComunicazione) {

		$dql = "SELECT ci "
				. "FROM IstruttorieBundle:ComunicazioneProgetto ci "
				. "JOIN ci.variazione var "
				. "JOIN var.attuazione_controllo_richiesta atc "
				. "JOIN atc.richiesta rich "
				. "JOIN ci.stato s "
				. "JOIN rich.procedura proc "
				. "LEFT JOIN rich.proponenti prop "
				. "LEFT JOIN ci.richieste_protocollo rp "
				. "LEFT JOIN prop.soggetto_version sv "			
				. "WHERE prop.mandatario=1 "
				;
		
		$q = $this->getEntityManager()->createQuery();

		if (!is_null($ricercaComunicazione->getSoggetto())) {
			$dql .= " AND prop.soggetto = :soggetto ";
			$q->setParameter("soggetto", $ricercaComunicazione->getSoggetto()->getId());		
		}
		
		if (!is_null($ricercaComunicazione->getProcedura())) {
			$dql .= " AND proc.id = :procedura ";
			$q->setParameter("procedura", $ricercaComunicazione->getProcedura()->getId());		
		}
		
		$dql .= " AND s.codice IN ('COM_PROTOCOLLATA') ";
		
		if (!is_null($ricercaComunicazione->getProtocollo())) {
			$dql .= "AND CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo ";
			$q->setParameter("protocollo", "%".$ricercaComunicazione->getProtocollo()."%");	
		}
		
		$q->setDQL($dql);

		$sql = $q->getSQL();
		return $q;
	}

	public function getElencoComunicazioniPrg(RicercaComunicazionePa $ricercaComunicazione) : Query	{
		$dql = "SELECT ci 
			FROM IstruttorieBundle:ComunicazioneProgetto ci 
			JOIN ci.richiesta rich 
			JOIN ci.stato s 
			JOIN rich.procedura proc 
			JOIN proc.asse asse 
			LEFT JOIN rich.proponenti prop 
			LEFT JOIN prop.soggetto sogg 
			LEFT JOIN rich.richieste_protocollo rp 
			LEFT JOIN prop.soggetto_version sv
            LEFT JOIN proc.stato_procedura proc_s
			WHERE prop.mandatario=1 
				AND sogg.denominazione like :soggetto
				AND proc.id = coalesce(:procedura, proc.id)
				AND asse.id = coalesce(:asse, asse.id)
				AND s.codice IN ('COM_PROTOCOLLATA')
				AND CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo
			";
        
        $utente = $ricercaComunicazione->getUtente();
		if (!is_null($utente)) {

			if (!$utente->hasRole("ROLE_SUPER_ADMIN")) {

				$dql .= " AND (proc_s.codice='CONCLUSO' OR proc.visibile_in_corso = 1) AND s.codice IN ('COM_INVIATA_PA','COM_PROTOCOLLATA') ";

				if (!$utente->hasRole("ROLE_ADMIN_PA")) {
					$dql .= " AND ( ";
					$dql .= "proc.id in (select proc3.id from SfingeBundle:PermessiProcedura proc2 join proc2.procedura proc3 where proc2.utente={$utente->getId()}) ";
					$dql .= "OR proc.asse in (select asse3.id from SfingeBundle:PermessiAsse asse2 join asse2.asse asse3 where asse2.utente={$utente->getId()}))";
				}
			}
		}
        
		$dql .= " ORDER BY ci.data_invio DESC ";
		
		$q = $this->getEntityManager()->createQuery($dql);
		$q->setParameter("soggetto", "%" . $ricercaComunicazione->getSoggetto() . "%");
		$q->setParameter("procedura", $ricercaComunicazione->getProcedura());;
		$q->setParameter("asse", $ricercaComunicazione->getAsse());
		$q->setParameter("protocollo", "%" . $ricercaComunicazione->getProtocollo() . "%");

		return $q;
	}	
	
	public function getElencoComunicazioniVar(RicercaComunicazionePa $ricercaComunicazione) {

		$dql = "SELECT ci "
				. "FROM IstruttorieBundle:ComunicazioneProgetto ci "
				. "JOIN ci.variazione var "
				. "JOIN var.attuazione_controllo_richiesta atc "
				. "JOIN atc.richiesta rich "
				. "JOIN ci.stato s "
				. "JOIN rich.procedura proc "
				. "JOIN proc.asse asse "
				. "LEFT JOIN rich.proponenti prop "
				. "LEFT JOIN prop.soggetto sogg "
				. "LEFT JOIN rich.richieste_protocollo rp "
				. "LEFT JOIN prop.soggetto_version sv "
                . "LEFT JOIN proc.stato_procedura proc_s "
				. "WHERE prop.mandatario=1 "
				;
		
        $utente = $ricercaComunicazione->getUtente();
		if (!is_null($utente)) {

			if (!$utente->hasRole("ROLE_SUPER_ADMIN")) {

				$dql .= " AND (proc_s.codice='CONCLUSO' OR proc.visibile_in_corso = 1) AND s.codice IN ('COM_INVIATA_PA','COM_PROTOCOLLATA') ";

				if (!$utente->hasRole("ROLE_ADMIN_PA")) {
					$dql .= " AND ( ";
					$dql .= "proc.id in (select proc3.id from SfingeBundle:PermessiProcedura proc2 join proc2.procedura proc3 where proc2.utente={$utente->getId()}) ";
					$dql .= "OR proc.asse in (select asse3.id from SfingeBundle:PermessiAsse asse2 join asse2.asse asse3 where asse2.utente={$utente->getId()}))";
				}
			}
		}
        
		$q = $this->getEntityManager()->createQuery();

		if (!is_null($ricercaComunicazione->getSoggetto())) {
			$dql .= " AND sogg.denominazione like :soggetto ";
			$q->setParameter("soggetto", "%".$ricercaComunicazione->getSoggetto()."%");	;		
		}
		
		if (!is_null($ricercaComunicazione->getProcedura())) {
			$dql .= " AND proc.id = :procedura ";
			$q->setParameter("procedura", $ricercaComunicazione->getProcedura()->getId());		
		}
		
		if (!is_null($ricercaComunicazione->getAsse())) {
			$dql .= " AND asse.id = :asse ";
			$q->setParameter("asse", $ricercaComunicazione->getAsse()->getId());		
		}
		
		$dql .= " AND s.codice IN ('COM_PROTOCOLLATA') ";
		
		if (!is_null($ricercaComunicazione->getProtocollo())) {
			$dql .= "AND CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo ";
			$q->setParameter("protocollo", "%".$ricercaComunicazione->getProtocollo()."%");	
		}
		
		$dql .= " ORDER BY ci.data_invio DESC ";
		
		$q->setDQL($dql);

		$sql = $q->getSQL();
		return $q;
	}	
}
