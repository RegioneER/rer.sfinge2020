<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 13/10/17
 * Time: 12:29
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tc12_risultato_atteso")
 */
class TC12RisultatoAtteso extends TC12Classificazione {
    public function getTipo() {
        return "OBIETTIVO";
    }
}
