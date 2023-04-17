<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tipologie_spesa")
 */
class TipologiaSpesa extends EntityTipo
{
    
    public function __toString() {
        return $this->descrizione;
    }
    
}

