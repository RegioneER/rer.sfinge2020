<?php

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class StatoComunicazionePagamento extends Stato
{
    const COM_PAG_INSERITA = "COM_PAG_INSERITA";
    const COM_PAG_VALIDATA = "COM_PAG_VALIDATA";
    const COM_PAG_FIRMATA = "COM_PAG_FIRMATA";
    const COM_PAG_INVIATA_PA = "COM_PAG_INVIATA_PA";
    const COM_PAG_PROTOCOLLATA = "COM_PAG_PROTOCOLLATA";
}
