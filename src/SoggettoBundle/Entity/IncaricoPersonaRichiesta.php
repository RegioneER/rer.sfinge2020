<?php

namespace SoggettoBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use RichiesteBundle\Entity\Richiesta;

/**
 * @ORM\Entity(repositoryClass="SoggettoBundle\Repository\IncaricoPersonaRichiestaRepository")
 * @ORM\Table(name="incarichi_persone_richieste")
 */
class IncaricoPersonaRichiesta extends EntityLoggabileCancellabile {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\IncaricoPersona", inversedBy="incarichi_richiesta")
     * @ORM\JoinColumn(name="incarico_id", referencedColumnName="id", nullable=false)
     * @var IncaricoPersona
     */
    protected $incarico_persona;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="incarichi_richiesta")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
     * @var Richiesta
     */
    protected $richiesta;

    public function __construct(Richiesta $richiesta) {
        $this->richiesta = $richiesta;
    }

    public function getId() {
        return $this->id;
    }

    public function getIncaricoPersona(): ?IncaricoPersona {
        return $this->incarico_persona;
    }

    public function getRichiesta(): Richiesta {
        return $this->richiesta;
    }

    public function setIncaricoPersona(IncaricoPersona $incarico_persona): self {
		$this->incarico_persona = $incarico_persona;
		
		return $this;
    }

    public function setRichiesta(Richiesta $richiesta): self {
		$this->richiesta = $richiesta;
		
		return $this;
    }
}
