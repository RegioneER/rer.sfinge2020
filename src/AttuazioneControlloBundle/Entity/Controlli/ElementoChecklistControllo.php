<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="elementi_checklist_controlli")
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Controlli\ElementoChecklistControlloRepository")
 */
class ElementoChecklistControllo {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="SezioneChecklistControllo", inversedBy="elementi")
     * @ORM\JoinColumn(nullable=false)
     * @var SezioneChecklistControllo
     */
    protected $sezione_checklist;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string|null
     */
    protected $descrizione;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $note;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string|null
     */
    protected $tipo;

    /**
     * @ORM\Column(type="array", name="choices", nullable=true)
	 * @var array|null
     */
    protected $choices;

    /**
     * @ORM\OneToMany(targetEntity="ValutazioneElementoChecklistControllo", mappedBy="elemento", cascade={"persist"})
	 * @var Collection|ValutazioneElementoChecklistControllo[]
     */
    protected $valutazioni;

    /**
     * @ORM\Column(type="integer", nullable=true)
	 * @var int|null
     */
    protected $lunghezza_massima;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    protected $codice;

    /**
     * @ORM\Column(type="array", name="procedure_operative", nullable=true)
     */
    protected $procedure;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $specifica;

    public function __construct() {
        $this->valutazioni = new \Doctrine\Common\Collections\ArrayCollection();
        $this->procedure = [];
    }

    public function getId() {
        return $this->id;
    }

    public function getSezioneChecklist(): ?SezioneChecklistControllo {
        return $this->sezione_checklist;
    }

    public function getDescrizione(): ?string  {
        return $this->descrizione;
    }

    public function getNote(): ?string {
        return $this->note;
    }

    public function getTipo(): ?string {
        return $this->tipo;
    }

    public function getChoices(): ?array {
        return $this->choices;
    }

    public function setSezioneChecklist(?SezioneChecklistControllo $sezione_checklist) {
        $this->sezione_checklist = $sezione_checklist;
    }

    public function setDescrizione(?string $descrizione): self {
		$this->descrizione = $descrizione;
		
		return $this;
    }

    public function setNote(?string $note) {
        $this->note = $note;
    }

    public function setTipo(?string $tipo) {
        $this->tipo = $tipo;
    }

    public function setChoices(array $choices) {
        $this->choices = $choices;
    }

	/**
	 * @return Collection|ValutazioneElementoChecklistControllo[]
	 */
    public function getValutazioni(): Collection {
        return $this->valutazioni;
    }

    public function setValutazioni(Collection $valutazioni) {
        $this->valutazioni = $valutazioni;
    }

    public function getCodice(): ?string {
        return $this->codice;
    }

    public function setCodice(?string $codice) {
        $this->codice = $codice;
    }

    public function getLunghezzaMassima(): ?int {
        return $this->lunghezza_massima;
    }

    public function setLunghezzaMassima(?int $lunghezza_massima) {
        $this->lunghezza_massima = $lunghezza_massima;
        return $this;
    }

    public function getProcedure(): array {
        return $this->procedure ?: [];
    }

    public function setProcedure(array $procedure) {
        $this->procedure = $procedure;
    }

    public function getSpecifica(): bool {
        return true == $this->specifica;
    }

    public function setSpecifica(bool $specifica) {
        $this->specifica = $specifica;
    }

    public function isSpecifica(): bool {
        return true == $this->specifica;
    }
}
