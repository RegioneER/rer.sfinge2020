<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 11/01/16
 * Time: 11:38
 */

namespace SoggettoBundle\Entity;


use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping AS ORM;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="stati_incarico")
 */
class StatoIncarico extends EntityTipo
{
    const ATTIVO = "ATTIVO";
    const ATTESA_CONFERMA = "ATTESA_CONFERMA";
    const REVOCATO = "REVOCATO";
    const BOCCIATO = "BOCCIATO";
}