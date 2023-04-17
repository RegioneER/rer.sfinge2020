<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use RichiesteBundle\Entity\VocePianoCosto;
use RichiesteBundle\Entity\Proponente;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\VariazionePianoCostiRepository")
 */
class VariazionePianoCosti extends VariazioneRichiesta {
    /**
     * @ORM\OneToMany(targetEntity="VariazioneVocePianoCosto", mappedBy="variazione", cascade={"persist", "remove"})
     * @var Collection|VariazioneVocePianoCosto[]
     */
    protected $voci_piano_costo;

    /**
     * @ORM\Column(name="costo_ammesso", type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $costo_ammesso;

    /**
     * @ORM\Column(name="contributo_ammesso", type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $contributo_ammesso;

    /** Aggiungiamo questa relazione per gestire le eventuali eccezioni per l'efficacia di una variazione rispetto al pagamento
     * se il pagameto ha una variazione associata allora definirà il piano costi bypassando tutti i controlli.
     * Andrebbe gestita come OneToOne ma purtroppo doctrine le gestisce male e rallenta il caricamento.
     * L'unica differenza a db è un vincolo di UNIQUE diciamo che è un compromesso non proprio piacevole
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", mappedBy="variazione")
     * @var Collection|Pagamento[]
     */
    protected $pagamento;

    public function __construct(AttuazioneControlloRichiesta $atc = null) {
        parent::__construct($atc);

        $this->voci_piano_costo = new ArrayCollection();
        $this->pagamento = new ArrayCollection();
    }

    /**
     * @return Collection|VariazioneVocePianoCosto[]
     */
    public function getVociPianoCosto() {
        return $this->voci_piano_costo;
    }

    public function setVociPianoCosto($voci_piano_costo) {
        $this->voci_piano_costo = $voci_piano_costo;

        return $this;
    }

    public function addVocePianoCosto(VariazioneVocePianoCosto $voce_piano_costo): self {
        return $this->addVociPianoCosto($voce_piano_costo);
    }

    public function getVariazioneVocePianoCosto(VocePianoCosto $voce_piano_costo): ?VariazioneVocePianoCosto {
        foreach ($this->voci_piano_costo as $voce) {
            if ($voce->getVocePianoCosto() == $voce_piano_costo) {
                return $voce;
            }
        }

        return null;
    }

    public function addVociPianoCosto(VariazioneVocePianoCosto $vociPianoCosto): self {
        $this->voci_piano_costo[] = $vociPianoCosto;

        return $this;
    }

    public function removeVociPianoCosto(VariazioneVocePianoCosto $vociPianoCosto): void {
        $this->voci_piano_costo->removeElement($vociPianoCosto);
    }

    /**
     * @return Collection|VariazioneVocePianoCosto[]
     */
    public function getVociPianoCostoProponente(?Proponente $proponente = null): Collection {
        if (\is_null($proponente)) {
            return $this->voci_piano_costo;
        }

        $voci = [];

        foreach ($this->voci_piano_costo as $voce) {
            if ($voce->getVocePianoCosto()->getProponente() == $proponente) {
                $voci[] = $voce;
            }
        }

        usort($voci, function ($a, $b) {
            return $a->getVocePianoCosto()->getPianoCosto()->getOrdinamento() > $b->getVocePianoCosto()->getPianoCosto()->getOrdinamento();
        });

        return new ArrayCollection($voci);
    }

    public function getCostoAmmessoVariato(): ?float {
        // se è vuoto vuol dire che c'è stata una variazione, ma che non riguarda il piano costo
        // in questo caso il costo ammesso di riferimento è quello presente dentro istruttoria
        if ($this->voci_piano_costo->isEmpty()) {
            return null;
        }

        $costoAmmessoVariato = 0;
        foreach ($this->voci_piano_costo as $voce) {
            $vocePianoCosto = $voce->getVocePianoCosto();
            $pianoCosto = $vocePianoCosto->getPianoCosto();
            if ('TOT' != $pianoCosto->getCodice()) {
                $costoAmmessoVariato += $voce->sommaImportiApprovati();
            }
        }

        return $costoAmmessoVariato;
    }

    public function isEsitata(\DateTime $dataRiferimento = null): bool {
        $esitata = parent::isEsitata($dataRiferimento) && $this->getVociPianoCosto()->count() > 0;

        return $esitata;
    }

    public function addPagamento(Pagamento $pagamento): self {
        $this->pagamento[] = $pagamento;

        return $this;
    }

    public function removePagamento(Pagamento $pagamento): void {
        $this->pagamento->removeElement($pagamento);
    }

    public function getCostoAmmesso() {
        return $this->costo_ammesso;
    }

    public function getContributoAmmesso() {
        return $this->contributo_ammesso;
    }

    public function setCostoAmmesso($costo_ammesso): self {
        $this->costo_ammesso = $costo_ammesso;

        return $this;
    }

    public function setContributoAmmesso($contributo_ammesso) {
        $this->contributo_ammesso = $contributo_ammesso;
    }

    /**
     * @return Collection|Pagamento[]
     */
    public function getPagamento(): Collection {
        return $this->pagamento;
    }
}
