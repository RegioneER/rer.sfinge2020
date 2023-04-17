<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\Stato;

/**
 * @ORM\Entity()
 */
class StatoProroga extends Stato
{

    const PROROGA_INSERITA = "PROROGA_INSERITA";
    const PROROGA_VALIDATA = "PROROGA_VALIDATA";
    const PROROGA_FIRMATA = "PROROGA_FIRMATA";
    const PROROGA_INVIATA_PA = "PROROGA_INVIATA_PA";
    const PROROGA_PROTOCOLLATA = "PROROGA_PROTOCOLLATA";
    
    public function __toString() {
        return $this->getDescrizione();
    }
}
