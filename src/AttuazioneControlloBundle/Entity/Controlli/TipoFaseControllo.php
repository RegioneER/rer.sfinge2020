<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tipi_fase_controllo")
 */
class TipoFaseControllo extends EntityTipo
{
    public function __toString() {
        return $this->descrizione;
    }
    
}

