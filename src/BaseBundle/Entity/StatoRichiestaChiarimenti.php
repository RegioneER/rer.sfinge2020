<?php

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class StatoRichiestaChiarimenti extends Stato
{

    const RICH_CHIAR_INSERITA = "RICH_CHIAR_INSERITA";
    const RICH_CHIAR_VALIDATA = "RICH_CHIAR_VALIDATA";
    const RICH_CHIAR_FIRMATA = "RICH_CHIAR_FIRMATA";
    const RICH_CHIAR_INVIATA_PA = "RICH_CHIAR_INVIATA_PA";
    const RICH_CHIAR_PROTOCOLLATA = "RICH_CHIAR_PROTOCOLLATA";
}
