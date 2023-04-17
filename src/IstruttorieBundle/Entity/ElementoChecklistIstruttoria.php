<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use IstruttorieBundle\Entity\ValutazioneElementoChecklistIstruttoria;

/**
 * @ORM\Table(name="istruttorie_elementi_checklist")
 * @ORM\Entity
 */

class ElementoChecklistIstruttoria extends EntityLoggabileCancellabile
{
	const CHOICE = 'choice';
	const INTEGER = 'integer';
	const TIPO_CHOICE = 'choice';
    const TIPO_INTEGER = 'integer';
	
	/**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\SezioneChecklistIstruttoria", inversedBy="elementi")
     * @ORM\JoinColumn(nullable=false)
     * @var SezioneChecklistIstruttoria
     */
    protected $sezione_checklist;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string
     */
    protected $descrizione;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $note;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    protected $tipo;

    /**
     * @ORM\Column(type="array", name="choices", nullable=true)
     * @var array
     */
    protected $choices;

    /**
     * @ORM\Column(type="integer",  name="punteggio_minimo_ammissibilita", nullable=true)
     * @var int|null
     */
    protected $punteggio_minimo_ammissibilita;

    /**
     * @ORM\Column(type="integer",  name="punteggio_massimo", nullable=true)
     * @var int|null
     */
    protected $punteggio_massimo;
	
	 /**
     * @ORM\Column(type="integer",  name="punteggio_minimo", nullable=true)
     * @var int|null
     */
    protected $punteggio_minimo;

    /**
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\ValutazioneElementoChecklistIstruttoria", mappedBy="elemento", cascade={"persist"})
     * @var Collection|ValutazioneElementoChecklistIstruttoria[]
     */
    protected $valutazioni;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    protected $significativo;

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
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool|null
     */
    protected $opzionale;

    public function __construct() {
        $this->valutazioni = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getSezioneChecklist(): SezioneChecklistIstruttoria {
        return $this->sezione_checklist;
    }

    public function getDescrizione(): string {
        return $this->descrizione;
    }

    public function getNote(): ?string {
        return $this->note;
    }

    public function getTipo(): string {
        return $this->tipo;
    }

    public function getChoices(): array {
        return $this->choices;
    }

    public function getPunteggioMinimoAmmissibilita(): ?int {
        return $this->punteggio_minimo_ammissibilita;
    }

    public function getPunteggioMassimo(): ?int {
        return $this->punteggio_massimo;
    }

    public function setId(int $id): self {
        $this->id = $id;

        return $this;
    }

    public function setSezioneChecklist(SezioneChecklistIstruttoria $sezione_checklist): self {
        $this->sezione_checklist = $sezione_checklist;

        return $this;
    }

    public function setDescrizione(string $descrizione): self {
        $this->descrizione = $descrizione;

        return $this;
    }

    public function setNote(?string $note): self {
        $this->note = $note;

        return $this;
    }

    public function setTipo(string $tipo): self {
        $this->tipo = $tipo;

        return $this;
    }

    public function setChoices(array $choices): self {
        $this->choices = $choices;

        return $this;
    }

    public function setPunteggioMinimoAmmissibilita(?int $punteggio_minimo_ammissibilita): self {
        $this->punteggio_minimo_ammissibilita = $punteggio_minimo_ammissibilita;

        return $this;
    }

    public function setPunteggioMassimo(?int $punteggio_massimo): self {
        $this->punteggio_massimo = $punteggio_massimo;

        return $this;
    }

    public function getValutazioni(): Collection {
        return $this->valutazioni;
    }

    public function setValutazioni(Collection $valutazioni): self {
        $this->valutazioni = $valutazioni;

        return $this;
    }

    public function getLunghezzaMassima(): ?int {
        return $this->lunghezza_massima;
    }

    public function setLunghezzaMassima(?int $lunghezza_massima): self {
        $this->lunghezza_massima = $lunghezza_massima;

        return $this;
    }

    public function getSignificativo(): bool {
        return $this->significativo;
    }

    public function setSignificativo(bool $significativo): self {
        $this->significativo = $significativo;

        return $this;
    }

    public function getCodice(): ?string {
        return $this->codice;
    }

    public function setCodice(?string $codice): self {
        $this->codice = $codice;

        return $this;
    }   
    
    public function getOpzionale() {
        return $this->opzionale;
    }

    public function setOpzionale(?bool $opzionale): self {
        $this->opzionale = $opzionale;

        return $this;
    }


    public function setPunteggioMinimo(?int $punteggioMinimo): self
    {
        $this->punteggio_minimo = $punteggioMinimo;

        return $this;
    }

    public function getPunteggioMinimo(): ?int
    {
        return $this->punteggio_minimo;
    }

    public function addValutazioni(ValutazioneElementoChecklistIstruttoria $valutazioni): self
    {
        $this->valutazioni[] = $valutazioni;

        return $this;
    }

    public function removeValutazioni(ValutazioneElementoChecklistIstruttoria $valutazioni): void
    {
        $this->valutazioni->removeElement($valutazioni);
    }
}
