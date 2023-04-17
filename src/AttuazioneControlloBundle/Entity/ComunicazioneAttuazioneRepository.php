<?php

namespace AttuazioneControlloBundle\Entity;
use Doctrine\ORM\EntityRepository;
use IstruttorieBundle\Form\Entity\RicercaComunicazionePa;
use Doctrine\ORM\Query;

class ComunicazioneAttuazioneRepository extends EntityRepository {
		
	public function getElencoComunicazioni( $ricercaComunicazione) {

		$dql = "SELECT ci "
				. "FROM AttuazioneControlloBundle:ComunicazioneAttuazione ci "
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

		return $q;
	}	

	public function getElencoComunicazioniPrg(RicercaComunicazionePa $ricercaComunicazione) : Query	{
		$dql = "SELECT ci 
			FROM AttuazioneControlloBundle:ComunicazioneAttuazione ci 
			JOIN ci.richiesta rich 
			JOIN ci.stato s 
			JOIN rich.procedura proc 
			JOIN proc.asse asse 
			LEFT JOIN rich.proponenti prop 
			LEFT JOIN prop.soggetto sogg 
			LEFT JOIN rich.richieste_protocollo rp 
			LEFT JOIN prop.soggetto_version sv 
			WHERE prop.mandatario=1 
				AND sogg.denominazione like :soggetto
				AND proc.id = coalesce(:procedura, proc.id)
				AND asse.id = coalesce(:asse, asse.id)
				AND s.codice IN ('COM_PROTOCOLLATA')
				AND CONCAT(rp.registro_pg, '/' , rp.anno_pg , '/' , rp.num_pg) LIKE :protocollo
			";
		$dql .= " ORDER BY ci.data_invio DESC ";
		
		$q = $this->getEntityManager()->createQuery($dql);
		$q->setParameter("soggetto", "%" . $ricercaComunicazione->getSoggetto() . "%");
		$q->setParameter("procedura", $ricercaComunicazione->getProcedura());;
		$q->setParameter("asse", $ricercaComunicazione->getAsse());
		$q->setParameter("protocollo", "%" . $ricercaComunicazione->getProtocollo() . "%");

		return $q;
	}	
}
