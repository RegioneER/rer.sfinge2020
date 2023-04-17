<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tipi_esito_controllo")
 */
class TipoEsitoControllo extends EntityTipo
{
    public function __toString() {
        return $this->descrizione;
    }
    
}

