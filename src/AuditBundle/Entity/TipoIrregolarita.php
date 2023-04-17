<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityTipo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="audit_tipi_irregolarita")
 */
class TipoIrregolarita extends EntityTipo {

    public function __toString() {
        return $this->getCodice(). " - ".$this->getDescrizione();
    }
  
}
