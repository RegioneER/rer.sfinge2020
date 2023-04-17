<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:51
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC45IndicatoriOutputProgrammaRepository")
 * @ORM\Table(name="tc45_indicatori_output_programma")
 */
class TC45IndicatoriOutputProgramma extends TC44_45IndicatoriOutput {
    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\Length(max=80, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_indicatore_out;

    /**
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id")
     */
    protected $programma;

    /**
     * @return mixed
     */
    public function getCodIndicatoreOut() {
        return $this->cod_indicatore_out;
    }

    /**
     * @param mixed $cod_indicatore_out
     */
    public function setCodIndicatoreOut($cod_indicatore_out) {
        $this->cod_indicatore_out = $cod_indicatore_out;
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
