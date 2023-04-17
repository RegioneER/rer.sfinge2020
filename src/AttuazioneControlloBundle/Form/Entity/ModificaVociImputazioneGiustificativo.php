<?php

namespace AttuazioneControlloBundle\Form\Entity;

use AttuazioneControlloBundle\Entity\GiustificativoPagamento;
use AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class ModificaVociImputazioneGiustificativo {
    /**
     * @var GiustificativoPagamento
     * @Assert\NotNull
     */
    protected $giustificativo;

    /**
     * @var Collection|VocePianoCostoGiustificativo[]
     */
    protected $voci;

    public function __construct(GiustificativoPagamento $giustificativo) {
        $this->giustificativo = $giustificativo;
        $this->voci = $this->giustificativo->getVociPianoCosto()->map(function (VocePianoCostoGiustificativo $voce) {
            $clone = clone $voce;
            return $clone;
        });
    }

    public function getVoci(): Collection {
        return $this->voci;
    }

    public function addVocus(VocePianoCostoGiustificativo $voce): self {
        $this->voci[] = $voce;
        return $this;
    }

    public function removeVocus(VocePianoCostoGiustificativo $voce): void {
        $this->voci->removeElement($voce);
    }

    public function getGiustificativo(): GiustificativoPagamento {
        return $this->giustificativo;
    }
}
