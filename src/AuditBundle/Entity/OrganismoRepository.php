<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\EntityRepository;


class OrganismoRepository extends EntityRepository {

	public function findNotInPianificazione($id_pianificazione) {
		
		$dql = "SELECT org FROM AuditBundle:Organismo org "
				. " WHERE org.id not in ("
				. " SELECT pian_org.id FROM AuditBundle:AuditOrganismo pian_org"
				. " join pian_org.audit pian "
				. " WHERE pian.id = $id_pianificazione) ";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q->getResult();
	}

}
