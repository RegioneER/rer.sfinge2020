<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 25/10/17
 * Time: 16:12
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\Id;
use RichiesteBundle\Entity\Richiesta;

/**
 * @ORM\Entity
 * @ORM\Table(name="richieste_indicatorioutput")
 */
class RichiestaIndicatoreOutput {
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $richiesta;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\IndicatoriOutputAzioni", inversedBy="richieste_indicatori_output")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $indicatore_output;

    public function getRichiesta(): ?Richiesta {
        return $this->richiesta;
    }

    public function setRichiesta(Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }

    public function getIndicatoreOutput(): ?IndicatoriOutputAzioni {
        return $this->indicatore_output;
    }

    public function setIndicatoreOutput(IndicatoriOutputAzioni $indicatore_output): self {
        $this->indicatore_output = $indicatore_output;

        return $this;
    }
}
