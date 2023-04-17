<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tipologie_stazioni_appaltanti")
 */
class TipologiaStazioneAppaltante extends EntityTipo
{
    
    public function __toString() {
        return $this->descrizione;
    }
    
}

