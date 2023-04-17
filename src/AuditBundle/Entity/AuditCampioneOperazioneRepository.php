<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AuditCampioneOperazioneRepository extends EntityRepository {

	public function findByOperazione($operazione) {	
		$dql = "SELECT acp FROM AuditBundle:AuditCampioneOperazione acp "
			. " WHERE acp.audit_operazione = {$operazione->getId()}";

		$q = $this->getEntityManager()->createQuery();
		$q->setDQL($dql);

		return $q;
	}

}
