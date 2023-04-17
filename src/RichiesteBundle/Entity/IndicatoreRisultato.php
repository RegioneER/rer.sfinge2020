<?php

namespace RichiesteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use MonitoraggioBundle\Entity\TC42_43IndicatoriRisultato;

/**
 * @ORM\Entity(repositoryClass="RichiesteBundle\Repository\IndicatoreRisultatoRepository")
 * @ORM\Table(name="indicatori_risultato",
 * uniqueConstraints={
 *      @ORM\UniqueConstraint(columns={"richiesta_id", "indicatore_id", "data_cancellazione"})
 * })
 */
class IndicatoreRisultato extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @var \RichiesteBundle\Entity\Richiesta
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="mon_indicatore_risultato")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     * @Assert\Valid
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC42_43IndicatoriRisultato")
     * @ORM\JoinColumn(name="indicatore_id", referencedColumnName="id")
     * @Assert\NotNull
     * @var TC42_43IndicatoriRisultato
     */
    protected $indicatore;

    /**
     * @param Richiesta $richiesta = NULL
     * @param TC42_43IndicatoriRisultato $indicatore = NULL
     */
    public function __construct(?Richiesta $richiesta = null, ?TC42_43IndicatoriRisultato $indicatore = null) {
        $this->richiesta = $richiesta;
        $this->indicatore = $indicatore;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    public function getRichiesta(): ?Richiesta {
        return $this->richiesta;
    }

    public function getIndicatore(): ?TC42_43IndicatoriRisultato {
        return $this->indicatore;
    }

    /**
     * @param int $id
     */
    public function setId($id): self {
        $this->id = $id;
        return $this;
    }

    public function setRichiesta(Richiesta $richiesta): self {
        $this->richiesta = $richiesta;
        return $this;
    }

    public function setIndicatore(TC42_43IndicatoriRisultato $indicatore): self {
        $this->indicatore = $indicatore;
        return $this;
    }
}
