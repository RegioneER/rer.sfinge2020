<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class VariazioneReferente extends VariazioneRichiesta {
    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="VariazioneSingoloReferente", mappedBy="variazione")
     */
    protected $variazioni_singolo_referente;

    public function __construct(AttuazioneControlloRichiesta $atc = null) {
        parent::__construct($atc);
        $this->variazioni_singolo_referente = new ArrayCollection();
    }

    public function addVariazioniSingoloReferente(VariazioneSingoloReferente $variazioniSingoloReferente): self {
        $this->variazioni_singolo_referente[] = $variazioniSingoloReferente;

        return $this;
    }

    public function removeVariazioniSingoloReferente(VariazioneSingoloReferente $variazioniSingoloReferente): void {
        $this->variazioni_singolo_referente->removeElement($variazioniSingoloReferente);
    }

    public function getVariazioniSingoloReferente(): Collection {
        return $this->variazioni_singolo_referente;
    }
}
