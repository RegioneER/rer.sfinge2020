<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\EconomiaRepository")
 * @ORM\Table(name="economie",
 * uniqueConstraints={
 *      @ORM\UniqueConstraint(columns={"richiesta_id", "fondo_id", "data_cancellazione"})
 * })
 */
class Economia extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Richiesta
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", cascade={"persist"}, inversedBy="mon_economie")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     */
    protected $richiesta;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC33FonteFinanziaria")
     * @ORM\JoinColumn(name="fondo_id", nullable=true)
     * @Assert\NotNull
     */
    protected $tc33_fonte_finanziaria;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull
     */
    protected $importo;

    public function __construct(Richiesta $richiesta = null, TC33FonteFinanziaria $fondo = null, float $importo = 0.0){
        $this->richiesta = $richiesta;
        $this->tc33_fonte_finanziaria = $fondo;
        $this->importo = $importo;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getRichiesta(): ?Richiesta {
        return $this->richiesta;
    }

    public function getTc33FonteFinanziaria(): ?TC33FonteFinanziaria {
        return $this->tc33_fonte_finanziaria;
    }

    public function getImporto(): ?float {
        return $this->importo;
    }

    public function setId(?int $id) {
        $this->id = $id;
    }

    public function setRichiesta(?Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }

    public function setTc33FonteFinanziaria(?TC33FonteFinanziaria $tc33_fonte_finanziaria) {
        $this->tc33_fonte_finanziaria = $tc33_fonte_finanziaria;
    }

    public function setImporto(?float $importo): self {
        $this->importo = $importo;

        return $this;
    }
}
