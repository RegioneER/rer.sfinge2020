<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityTipo;

/**
 * @ORM\Table(name="audit_tipi")
 * @ORM\Entity()
 */
class TipoAudit extends EntityTipo {
	
}
