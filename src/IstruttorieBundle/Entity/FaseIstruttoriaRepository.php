<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\EntityRepository;

class FaseIstruttoriaRepository extends EntityRepository {
	
	public function findFaseSuccessiva($istruttoria_richiesta)
	{
		
		$dql = "SELECT fase FROM IstruttorieBundle:FaseIstruttoria fase "
				. "WHERE fase.procedura = {$istruttoria_richiesta->getRichiesta()->getProcedura()->getId()}";
				
		if (!is_null($istruttoria_richiesta->getFase())) {
			$dql .= " AND fase.step > {$istruttoria_richiesta->getFase()->getStep()}";
		}
				
		$dql .= " ORDER BY fase.step ASC";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);
		
		$q->setMaxResults(1);
		
		$results = $q->getResult();

		return count($results) > 0 ? $results[0] : null;
	}	
}
