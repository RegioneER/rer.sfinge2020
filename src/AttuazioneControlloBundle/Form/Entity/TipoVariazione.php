<?php

namespace AttuazioneControlloBundle\Form\Entity;

use AnagraficheBundle\Entity\Persona;
use Symfony\Component\Validator\Constraints as Assert;
use AttuazioneControlloBundle\Entity\VariazioneRichiesta;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use RichiesteBundle\Entity\Richiesta;

class TipoVariazione {
    /**
     * @var Persona|null
     * @assert\NotNull
     */
    protected $firmatario;

    /**
     * @var string|null
     * @assert\NotNull
     * @Assert\Choice(VariazioneRichiesta::TIPI_VARIAZIONI)
     */
    protected $tipoVariazione;

    /**
     * @var AttuazioneControlloRichiesta
     */
    protected $atc;

    public function getFirmatario(): ?Persona {
        return $this->firmatario;
    }

    public function setFirmatario(Persona $firmatario): self {
        $this->firmatario = $firmatario;

        return $this;
    }

    public function getTipoVariazione(): ?string {
        return $this->tipoVariazione;
    }

    public function setTipoVariazione(string $tipo): self {
        $this->tipoVariazione = $tipo;

        return $this;
    }

    public function __construct(AttuazioneControlloRichiesta $atc) {
        $this->atc = $atc;
    }

    public function getIstanzaVariazione(): VariazioneRichiesta {
        $reflClassVariazione = new \ReflectionClass($this->tipoVariazione);
        /** @var VariazioneRichiesta $variazione */
        $variazione = $reflClassVariazione->newInstance($this->atc);
        $variazione->setFirmatario($this->firmatario);

        return $variazione;
    }

    /**
     * @Assert\IsFalse(message="E' già presente una variazione pendente di questo tipo")
     */
    public function isTipoVariazionePresente(): bool {
        return \is_null($this->tipoVariazione) ? false : $this->atc->hasVariazionePendente($this->tipoVariazione);
    }

    public function getRichiesta(): Richiesta
    {
        return $this->atc->getRichiesta();
    }

    /**
     * @return AttuazioneControlloRichiesta
     */
    public function getAtc(): AttuazioneControlloRichiesta
    {
        return $this->atc;
    }
}
