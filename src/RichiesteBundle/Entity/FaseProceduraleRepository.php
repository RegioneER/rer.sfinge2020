<?php


namespace RichiesteBundle\Entity;

use Doctrine\ORM\EntityRepository;

class FaseProceduraleRepository extends EntityRepository {
	
	public function getFasiDaProcedura($id_procedura) {

		$dql = "SELECT fase FROM RichiesteBundle:FaseProcedurale fase "
				. "JOIN fase.procedura proc "
				. "WHERE proc.id = $id_procedura";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

}
