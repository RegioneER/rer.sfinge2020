<?php

namespace AttuazioneControlloBundle\Entity\Revoche;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityTipo;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="tipi_motivazione_revoche")
 */
class TipoMotivazioneRevoca extends EntityTipo
{
    public function __toString() {
        return $this->descrizione;
    }
}