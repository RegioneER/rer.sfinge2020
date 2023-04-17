<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 05/01/16
 * Time: 16:09
 */

namespace RichiesteBundle\Form\Entity\Bando2;

use AnagraficheBundle\Entity\Persona;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaBeniCulturaliNaturali {
    /**
     * @Assert\NotBlank
     */
    protected $firmatario;

    /**
     * @Assert\NotBlank
     * @var string|null
     */
    protected $tipologia;

    /**
     * @Assert\NotNull
     * @var bool
     */
    protected $multi_proponente = false;

    /**
     * @Assert\NotNull
     * @var bool
     */
    protected $progetto_integrato = false;

    public function getTipologia(): ?string {
        return $this->tipologia;
    }

    public function setTipologia(string $tipologia): void {
        $this->tipologia = $tipologia;
    }

    public function getMultiProponente(): bool {
        return $this->multi_proponente;
    }

    public function setMultiProponente(bool $multi_proponente): void {
        $this->multi_proponente = $multi_proponente;
    }

    public function getProgettoIntegrato(): bool {
        return $this->progetto_integrato;
    }

    public function setProgettoIntegrato(bool $value): void {
        $this->progetto_integrato = $value;
    }

    public function getFirmatario(): ?Persona {
        return $this->firmatario;
    }

    public function setFirmatario(Persona $firmatario) {
        $this->firmatario = $firmatario;
    }
}
