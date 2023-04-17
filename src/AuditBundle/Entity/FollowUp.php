<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityTipo;

/**
 * @ORM\Table(name="audit_follow_up")
 * @ORM\Entity()
 */
class FollowUp extends EntityTipo {
	
	public function __toString() {
		return $this->getDescrizione();
	}
}
