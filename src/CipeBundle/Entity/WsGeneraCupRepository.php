<?php

namespace CipeBundle\Entity;

use Doctrine\ORM\EntityRepository;
use CipeBundle\Entity\Ricerche\RicercaWsGeneraCup;



class WsGeneraCupRepository extends EntityRepository {

	protected function isNotNullAndEmpty($param) {
		$st = (!\is_null($param) &&  (\is_bool($param) || trim($param) != '')) ? true : false;
		return $st;
	}
	
	protected function getDqlFilterParam($param) {
		$dql = " = $param";
		if(\is_null($param))	$dql = " IS NULL";
		if($param === true)		$dql = " = 1";
		if($param === false)	$dql = " = 0";
		if(\is_string($param))	$dql = " = '$param'";
		return $dql;
	}
	
	public function ricercaWsGeneraCup(RicercaWsGeneraCup $RicercaWsGeneraCup) {
		try {
				$dql = "SELECT ws FROM CipeBundle:WsGeneraCup ws";

				$whereCriteria = array();
				if($this->isNotNullAndEmpty($RicercaWsGeneraCup->getCurlError()))
					$whereCriteria[] = "ws.curlError".$this->getDqlFilterParam($RicercaWsGeneraCup->getCurlError());

				if($this->isNotNullAndEmpty($RicercaWsGeneraCup->getEsito()))				
					$whereCriteria[] = "ws.esito".$this->getDqlFilterParam($RicercaWsGeneraCup->getEsito());

				if($this->isNotNullAndEmpty($RicercaWsGeneraCup->getIdProgetto()))			
					$whereCriteria[] = "ws.idProgetto".$this->getDqlFilterParam($RicercaWsGeneraCup->getIdProgetto());
				
				if($this->isNotNullAndEmpty($RicercaWsGeneraCup->getIdRichiesta()))			
					$whereCriteria[] = "ws.idRichiesta".$this->getDqlFilterParam($RicercaWsGeneraCup->getIdRichiesta());
				
				if($this->isNotNullAndEmpty($RicercaWsGeneraCup->getRichiestaInoltrata()) && $RicercaWsGeneraCup->getRichiestaInoltrata() === true)	
					$whereCriteria[] = "ws.timeStampRisposta IS NOT NULL";
				
				if($this->isNotNullAndEmpty($RicercaWsGeneraCup->getRichiestaValida()))		
					$whereCriteria[] = "ws.richiestaValida".$this->getDqlFilterParam($RicercaWsGeneraCup->getRichiestaValida());
				
				if(count($whereCriteria) >0) {
					$dql.= " WHERE ". implode(" AND ", $whereCriteria);
				}
				$q = $this->getEntityManager()->createQuery();
				$q->setDQL($dql);
				$result = $q->getResult();
				return $result;
		} catch (\Exception $ex) {
			return array();
		}

	}


}
