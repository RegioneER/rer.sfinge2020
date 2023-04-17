<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\Stato;

/**
 * @ORM\Entity()
 */
class StatoVariazione extends Stato
{

    const VAR_INSERITA = "VAR_INSERITA";
    const VAR_VALIDATA = "VAR_VALIDATA";
    const VAR_FIRMATA = "VAR_FIRMATA";
    const VAR_INVIATA_PA = "VAR_INVIATA_PA";
    const VAR_PROTOCOLLATA = "VAR_PROTOCOLLATA";
    
    public function __toString() {
        return $this->getDescrizione();
    }
}
