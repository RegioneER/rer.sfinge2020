<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RichiesteBundle\Entity\Proponente;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\VariazioneDatiBancariRepository")
 */
class VariazioneDatiBancari extends VariazioneRichiesta {
    /**
     * @ORM\OneToMany(targetEntity="VariazioneDatiBancariProponente", mappedBy="variazione", cascade={"persist", "remove"})
     * @var Collection|VariazioneDatiBancariProponente[]
     */
    protected $datiBancari;

    public function __construct(AttuazioneControlloRichiesta $atc) {
        parent::__construct($atc);

        $this->datiBancari = $atc->getRichiesta()->getProponenti()->map(function (Proponente $proponente) {
            $datiBancari = $proponente->getDatiBancari()->first();
            if ($datiBancari) {
                return new VariazioneDatiBancariProponente($this, $datiBancari);
            }
            return null;
        })->filter(function (?VariazioneDatiBancariProponente $variazione) {
            return !\is_null($variazione);
        });
    }

    public function addDatiBancari(VariazioneDatiBancariProponente $datiBancari): self {
        $this->datiBancari[] = $datiBancari;

        return $this;
    }

    public function removeDatiBancari(VariazioneDatiBancariProponente $datiBancari): void {
        $this->datiBancari->removeElement($datiBancari);
    }

    /**
     * @return Collection|VariazioneDatiBancariProponente[]
     */
    public function getDatiBancari(): Collection {
        return $this->datiBancari;
    }
}
