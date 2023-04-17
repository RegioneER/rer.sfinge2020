<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityTipo;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="tipi_iter_recuperi")
 */
class TipoIterRecupero extends EntityTipo
{	
    public function __toString() {
        return $this->descrizione;
    }
}