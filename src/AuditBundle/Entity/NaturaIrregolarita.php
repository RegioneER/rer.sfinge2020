<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityTipo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="audit_nature_irregolarita")
 */
class NaturaIrregolarita extends EntityTipo {
    
    public function __toString() {
        return $this->getDescrizione();
    }	
}
