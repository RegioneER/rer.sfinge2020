<?php

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class StatoEsitoIstruttoriaPagamento extends Stato
{

    const ESITO_IP_INSERITA     = "ESITO_IP_INSERITA";
    const ESITO_IP_INVIATA_PA   = "ESITO_IP_INVIATA_PA";
    const ESITO_IP_PROTOCOLLATA = "ESITO_IP_PROTOCOLLATA";
}
