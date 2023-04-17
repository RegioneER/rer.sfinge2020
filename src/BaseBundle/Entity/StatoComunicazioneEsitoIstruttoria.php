<?php

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class StatoComunicazioneEsitoIstruttoria extends Stato
{

    const ESI_INSERITA = "ESI_INSERITA";
	const ESI_VALIDATA = "ESI_VALIDATA";
	const ESI_FIRMATA = "ESI_FIRMATA";
    const ESI_INVIATA_PA = "ESI_INVIATA_PA";
    const ESI_PROTOCOLLATA = "ESI_PROTOCOLLATA";
}
