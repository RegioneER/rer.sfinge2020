<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityTipo;

/**
 * @ORM\Table(name="audit_tipi_campione")
 * @ORM\Entity()
 */
class TipoCampione extends EntityTipo {
	
	public function __toString() {
		return $this->getDescrizione();
	}
}
