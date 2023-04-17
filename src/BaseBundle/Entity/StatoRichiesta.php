<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 09/02/16
 * Time: 13:12
 */

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class StatoRichiesta extends Stato
{

    const PRE_INSERITA = "PRE_INSERITA";
    const PRE_VALIDATA = "PRE_VALIDATA";
    const PRE_FIRMATA = "PRE_FIRMATA";
    const PRE_INVIATA_PA = "PRE_INVIATA_PA";
    const PRE_PROTOCOLLATA = "PRE_PROTOCOLLATA";
}
