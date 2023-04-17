<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:50
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC44IndicatoriOutputComuniRepository")
 * @ORM\Table(name="tc44_indicatori_output_comuni")
 */
class TC44IndicatoriOutputComuni extends TC44_45IndicatoriOutput {
    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\Length(max=1, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $flag_calcolo;

    /**
     * @return mixed
     */
    public function getFlagCalcolo() {
        return $this->flag_calcolo;
    }

    /**
     * @param mixed $flag_calcolo
     */
    public function setFlagCalcolo($flag_calcolo) {
        $this->flag_calcolo = $flag_calcolo;
    }

    public function getTipo() {
        return "COMUNI";
    }
}
