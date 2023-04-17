<?php

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class StatoComunicazioneProgetto extends Stato
{

    const COM_INSERITA = "COM_INSERITA";
	const COM_VALIDATA = "COM_VALIDATA";
	const COM_FIRMATA = "COM_FIRMATA";
    const COM_INVIATA_PA = "COM_INVIATA_PA";
    const COM_PROTOCOLLATA = "COM_PROTOCOLLATA";
}
