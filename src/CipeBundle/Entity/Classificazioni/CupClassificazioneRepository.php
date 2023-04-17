<?php

namespace CipeBundle\Entity\Classificazioni;

use Doctrine\ORM\EntityRepository;
use CipeBundle\Entity\Ricerche\RicercaClassificazioneCipe;
use CipeBundle\Entity\Ricerche\RicercaClassificazioneCipeSettore;


class CupClassificazioneRepository extends EntityRepository {

	protected function isNotNullAndEmpty($param) {
		$st = (!\is_null($param) &&  (\is_bool($param) || trim($param) != '')) ? true : false;
		return $st;
	}
	
	protected function getDqlFilterParam($param, $like=null) {
		$dql = " = $param";
		if(\is_null($param))	$dql = " IS NULL";
		if($param === true)		$dql = " = 1";
		if($param === false)	$dql = " = 0";
		if(\is_string($param))	{
			if(!\is_null($like) && $like) $dql = " LIKE '%$param%'";
			else $dql = " = '$param'";
		}
		return $dql;
	}
	
	
	public function ricercaClassificazioneCipe(RicercaClassificazioneCipe $RicercaClassificazioneCipe) {
		try {
				$TipoClassificazione = $RicercaClassificazioneCipe->getTipoClassificazione();
				
				$dql = "SELECT tc FROM CipeBundle\Entity\Classificazioni\\$TipoClassificazione tc";

				$whereCriteria = array();
				if($this->isNotNullAndEmpty($RicercaClassificazioneCipe->getCodice()))
					$whereCriteria[] = "tc.codice".$this->getDqlFilterParam($RicercaClassificazioneCipe->getCodice());

				if($this->isNotNullAndEmpty($RicercaClassificazioneCipe->getDescrizione()))
					$whereCriteria[] = "tc.descrizione".$this->getDqlFilterParam($RicercaClassificazioneCipe->getDescrizione(), true);
				
				$addons_criteria = $RicercaClassificazioneCipe->getAddons_criteria();
				if(count($addons_criteria) >0) {
					foreach ($addons_criteria as $key => $value_array) {
						$value	= \array_key_exists("value", $value_array)	? $value_array['value'] : null;
						$like	= \array_key_exists("like", $value_array)	? $value_array['like']	: null;
						if($this->isNotNullAndEmpty($value)) $whereCriteria[] = "tc.$key".$this->getDqlFilterParam($value, $like);
					}
				}
				
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
	
	public function ricercaClassificazioneCipeSettore(RicercaClassificazioneCipeSettore $RicercaClassificazioneCipe) {
		try {
				$TipoClassificazione = $RicercaClassificazioneCipe->getTipoClassificazione();
				
				$dql = "SELECT tc FROM CipeBundle\Entity\Classificazioni\\$TipoClassificazione tc";

				$whereCriteria = array();
				if($this->isNotNullAndEmpty($RicercaClassificazioneCipe->getCodice()))
					$whereCriteria[] = "tc.codice".$this->getDqlFilterParam($RicercaClassificazioneCipe->getCodice());

				if($this->isNotNullAndEmpty($RicercaClassificazioneCipe->getDescrizione()))
					$whereCriteria[] = "tc.descrizione".$this->getDqlFilterParam($RicercaClassificazioneCipe->getDescrizione(), true);
				
				$addons_criteria = $RicercaClassificazioneCipe->getAddons_criteria();
				
				if(\array_key_exists("CupNatura", $addons_criteria)) {
					
					$value_array = $addons_criteria['CupNatura'];
					$value	= \array_key_exists("value", $value_array)	? $value_array['value'] : null;
					$like	= \array_key_exists("like", $value_array)	? $value_array['like']	: null;
					
					if($this->isNotNullAndEmpty($value)) {
							$dql.=" LEFT JOIN tc.CupNature cn";
							$whereCriteria[] = "cn.id".$this->getDqlFilterParam($value, $like);
					}
				}
				
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
