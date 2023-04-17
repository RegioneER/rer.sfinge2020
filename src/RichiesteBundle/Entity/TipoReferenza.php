<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 28/01/16
 * Time: 15:26
 */

namespace RichiesteBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityTipo;

/**
 * @ORM\Table(name="tipi_referenza")
 * @ORM\Entity()
 */
class TipoReferenza extends EntityTipo
{
}