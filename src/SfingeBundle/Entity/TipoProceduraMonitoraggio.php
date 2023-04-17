<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tipi_procedure_monitoraggio")
 */
class TipoProceduraMonitoraggio extends EntityTipo
{
    const BANDO = "BANDO";
    const CIRCOLARE = "CIRCOLARE";
    const AVVISO_EVIDENZA_PUBBLICA = "AVVISO_EVIDENZA_PUBBLICA";
    const MANIFESTAZIONE_INTERESSE = "MANIFESTAZIONE_INTERESSE";
    const PROCEDURA_NEGOZIALE = "PROCEDURA_NEGOZIALE";
    const INDIVIDUAZIONE_DIRETTA_PROGRAMMA = "INDIVIDUAZIONE_DIRETTA_PROGRAMMA";
}
