<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:46
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC42IndicatoriRisultatoComuniRepository")
 */
class TC42IndicatoriRisultatoComuni extends TC42_43IndicatoriRisultato {
    public function getTipo() {
        return "COMUNI";
    }
}
