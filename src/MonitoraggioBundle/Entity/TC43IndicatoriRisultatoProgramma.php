<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:48
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC43IndicatoriRisultatoProgrammaRepository")
 */
class TC43IndicatoriRisultatoProgramma extends TC42_43IndicatoriRisultato {
    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\Length(max=80, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_indicatore_ris;

    /**
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id")
     */
    protected $programma;

    /**
     * @return mixed
     */
    public function getCodIndicatoreRis() {
        return $this->cod_indicatore_ris;
    }

    /**
     * @param mixed $cod_indicatore_ris
     */
    public function setCodIndicatoreRis($cod_indicatore_ris) {
        $this->cod_indicatore_ris = $cod_indicatore_ris;
    }

    /**
     * @return mixed
     */
    public function getProgramma() {
        return $this->programma;
    }

    /**
     * @param mixed $cod_programma
     */
    public function setProgramma($cod_programma) {
        $this->programma = $cod_programma;
    }

    public function getTipo() {
        return "PROGRAMMA";
    }
}
