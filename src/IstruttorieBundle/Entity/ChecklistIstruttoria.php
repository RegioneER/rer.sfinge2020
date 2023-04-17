<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table(name="istruttorie_checklist")
 * @ORM\Entity(repositoryClass="IstruttorieBundle\Entity\ChecklistIstruttoriaRepository")
 */
class ChecklistIstruttoria {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\FaseIstruttoria", inversedBy="checklist")
     * @ORM\JoinColumn(nullable=false)
     * @var FaseIstruttoria
     */
    protected $fase;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    protected $codice;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    protected $nome;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    protected $ruolo;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    protected $molteplicita;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    protected $proponente;

    /**
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\SezioneChecklistIstruttoria", mappedBy="checklist")
     * @ORM\OrderBy({"ordinamento": "ASC"})
     * @var Collection|SezioneChecklistIstruttoria[]
     */
    protected $sezioni;

    /**
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\ValutazioneChecklistIstruttoria", mappedBy="checklist")
     * @var Collection|ValutazioneChecklistIstruttoria[]
     */
    protected $valutazioni_checklist;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool|null
     */
    protected $integrazione_interrompe_termini;

    public function __construct() {
        $this->sezioni = new ArrayCollection();
        $this->valutazioni_checklist = new ArrayCollection();
    }

    public function getId(): int {
        return $this->id;
    }

    public function getFase(): FaseIstruttoria {
        return $this->fase;
    }

    public function getCodice(): string {
        return $this->codice;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function getRuolo(): string {
        return $this->ruolo;
    }

    public function getMolteplicita(): int {
        return $this->molteplicita;
    }

    public function getProponente(): bool {
        return $this->proponente;
    }

    /**
     * @return Collection|SezioneChecklistIstruttoria[]
     */
    public function getSezioni(): Collection {
        return $this->sezioni;
    }

    /**
     * @return Collection|ValutazioneChecklistIstruttoria[]
     */
    public function getValutazioniChecklist(): Collection {
        return $this->valutazioni_checklist;
    }

    public function setId(int $id): self {
        $this->id = $id;

        return $this;
    }

    public function setFase(FaseIstruttoria $fase): self {
        $this->fase = $fase;

        return $this;
    }

    public function setCodice(string $codice): self {
        $this->codice = $codice;

        return $this;
    }

    public function setNome(string $nome): self {
        $this->nome = $nome;

        return $this;
    }

    public function setRuolo(string $ruolo): self {
        $this->ruolo = $ruolo;

        return $this;
    }

    public function setMolteplicita(int $molteplicita): self {
        $this->molteplicita = $molteplicita;

        return $this;
    }

    public function setProponente(bool $proponente): self {
        $this->proponente = $proponente;

        return $this;
    }

    public function setSezioni(Collection $sezioni): self {
        $this->sezioni = $sezioni;

        return $this;
    }

    public function setValutazioniChecklist(Collection $valutazioni_checklist): self {
        $this->valutazioni_checklist = $valutazioni_checklist;

        return $this;
    }

    public function getIntegrazioneInterrompeTermini(): ?bool {
        return $this->integrazione_interrompe_termini;
    }

    public function setIntegrazioneInterrompeTermini(?bool $integrazione_interrompe_termini): self {
        $this->integrazione_interrompe_termini = $integrazione_interrompe_termini;

        return $this;
    }

    public function __toString() {
        return $this->nome;
    }
}
