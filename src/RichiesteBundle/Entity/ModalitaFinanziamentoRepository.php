<?php


namespace RichiesteBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ModalitaFinanziamentoRepository extends EntityRepository {
	
	public function getModalitaDaProcedura($id_procedura) {

		$dql = "SELECT mod FROM RichiesteBundle:ModalitaFinanziamento mod "
				. "JOIN mod.procedura proc "
				. "WHERE proc.id = $id_procedura";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

}
