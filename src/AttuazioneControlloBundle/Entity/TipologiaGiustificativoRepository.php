<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\EntityRepository;
use AttuazioneControlloBundle\Entity\StatoVariazione;

class TipologiaGiustificativoRepository extends EntityRepository {

	
	/**
	 * Ho definito un set standard di tipologie giustificativo aventi codice con suffisso 'TIPOLOGIA_STANDARD_'
	 * 
	 * Sto prevedendo la possibilità di ridefinire il set per la specifica procedura..non si sa mai con sti pazzi
	 * quindi il ragionamento è: se seno presenti tipologie per la sepcifica procedura torno quelle
	 * altrimenti fetcho il set standard. 
	 */
	public function getTipologieGiustificativo($proceduraId) {

		$dql = "SELECT tg FROM AttuazioneControlloBundle:TipologiaGiustificativo tg "
				. "JOIN tg.procedure p "
				. "WHERE p.id = :proceduraId "
				. "AND (tg.invisibile IS NULL OR tg.invisibile = 0)  "
				. "ORDER BY tg.descrizione";
		
		$query = $this->getEntityManager()->createQuery($dql);
		$query->setParameter('proceduraId', $proceduraId);	
		
		$result = $query->getResult();
		if(count($result) > 0){
			return $result;
		}
		
		// se non trovo record fetcho il set standard
		$dql = "SELECT tg FROM AttuazioneControlloBundle:TipologiaGiustificativo tg "
				. "WHERE tg.codice LIKE :codice "
				. "AND (tg.invisibile IS NULL OR tg.invisibile = 0) "
				. "ORDER BY tg.descrizione";
		
		$query = $this->getEntityManager()->createQuery($dql);
		$query->setParameter('codice', "%" . "TIPOLOGIA_STANDARD_" . "%");	

		$result = $query->getResult();
		
		return $result;
	}

}
