<?php

namespace AttuazioneControlloBundle\Entity\Revoche;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityTipo;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="tipi_revoche")
 */
class TipoRevoca extends EntityTipo
{
    public function __toString() {
        return $this->descrizione;
    }
}