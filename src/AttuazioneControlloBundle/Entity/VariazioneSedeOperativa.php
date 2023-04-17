<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SoggettoBundle\Entity\Sede;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\VariazioneSedeOperativaRepository")
 */
class VariazioneSedeOperativa extends VariazioneRichiesta {
    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Sede")
     * @var Sede|null
     */
    protected $sede_operativa;
    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Sede")
     * @Assert\NotNull
     * @var Sede|null
     */
    protected $sede_operativa_variata;

    /**
     * @ORM\Column(type="boolean", name="autodichiarazione_sede")
     * @Assert\IsTrue
     * @var bool
     */
    protected $autodichiarazione = false;

    public function __construct(AttuazioneControlloRichiesta $atc = null, Sede $sede_operativa_corrente = null) {
        parent::__construct($atc);
        $this->sede_operativa = $sede_operativa_corrente;
    }

    public function setSedeOperativa(Sede $sedeOperativa = null): self {
        $this->sede_operativa = $sedeOperativa;

        return $this;
    }

    public function getSedeOperativa(): ?Sede {
        return $this->sede_operativa;
    }

    public function setSedeOperativaVariata(Sede $sedeOperativaVariata = null): self {
        $this->sede_operativa_variata = $sedeOperativaVariata;

        return $this;
    }

    public function getSedeOperativaVariata(): ?Sede {
        return $this->sede_operativa_variata;
    }

    public function setAutodichiarazione(bool $autodichiarazione): self {
        $this->autodichiarazione = $autodichiarazione;

        return $this;
    }

    public function getAutodichiarazione(): bool {
        return $this->autodichiarazione;
    }
}
