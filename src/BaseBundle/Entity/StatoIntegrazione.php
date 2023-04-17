<?php

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class StatoIntegrazione extends Stato
{

    const INT_INSERITA = "INT_INSERITA";
    const INT_VALIDATA = "INT_VALIDATA";
    const INT_FIRMATA = "INT_FIRMATA";
    const INT_INVIATA_PA = "INT_INVIATA_PA";
    const INT_PROTOCOLLATA = "INT_PROTOCOLLATA";
}
