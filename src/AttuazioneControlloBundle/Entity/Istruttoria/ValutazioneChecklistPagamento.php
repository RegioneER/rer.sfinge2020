<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\Pagamento;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use SfingeBundle\Entity\Utente;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamentoRepository")
 * @ORM\Table(name="valutazioni_checklist_pagamenti")
 */
class ValutazioneChecklistPagamento extends EntityLoggabileCancellabile
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
		
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="valutazioni_checklist")
	 * @ORM\JoinColumn(nullable=false)
	 * @var Pagamento
	 */
	protected $pagamento;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento", inversedBy="valutazioni_checklist")
	 * @ORM\JoinColumn(nullable=false)
	 * @var ChecklistPagamento|null
	 */
	protected $checklist;

	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente")
	 * @ORM\JoinColumn(nullable=true)
	 * @var Utente|null
	 *
	 */
	protected $valutatore;
	
	/**
	 * @ORM\Column(type="boolean", nullable=false)
	 * @var bool
	 */
	protected $validata;
	
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @var bool|null
	 */
	protected $ammissibile;	
	
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneElementoChecklistPagamento", mappedBy="valutazione_checklist", cascade={"persist"})
	 * @Assert\Valid
	 * @var Collection
	 */
	private $valutazioni_elementi;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @var int|null
	 */
	protected $punteggio;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @var \DateTime|null
	 */	
	protected $data_validazione;
			
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\StoricoAzioniValutazioneChecklistPagamento", cascade={"persist"}, mappedBy="valutazioneChecklistPagamento")
	 * @ORM\OrderBy({"data" = "ASC"})
	 * @var Collection
	 */
	protected $storicoAzioni;
	
	/**
	 * campo non mappato di supporto al formtype della checklist
	 */
	public $notaInvalidazione;
	
	/**
	 * dove previste, alle checklist appalti vanno associati dei documenti (le checklist di autovalutazione)
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\DocumentoChecklistPagamento", mappedBy="valutazioneChecklistPagamento", cascade={"persist", "remove"})
	 * @var Collection
	 */
	protected $documenti_checklist;
	
	function __construct() {
		$this->valutazioni_elementi = new ArrayCollection();
		$this->storicoAzioni = new ArrayCollection();
		$this->documenti_checklist = new ArrayCollection();
	}
	
	function getId() {
		return $this->id;
	}

	function getChecklist(): ?ChecklistPagamento {
		return $this->checklist;
	}

	function getValutatore(): ?Utente {
		return $this->valutatore;
	}

	function getValidata(): bool {
		return $this->validata;
	}

	function getAmmissibile(): ?bool {
		return $this->ammissibile;
	}

	function getValutazioniElementi(): Collection {
		return $this->valutazioni_elementi;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setChecklist(?ChecklistPagamento $checklist): self {
		$this->checklist = $checklist;

		return $this;
	}

	function setValutatore(?Utente $valutatore): self {
		$this->valutatore = $valutatore;

		return $this;
	}

	function setValidata(bool $validata): self {
		$this->validata = $validata;

		return $this;
	}

	function setAmmissibile(?bool $ammissibile): self {
		$this->ammissibile = $ammissibile;

		return $this;
	}

	function setValutazioniElementi(Collection $valutazioni_elementi): self {
		$this->valutazioni_elementi = $valutazioni_elementi;

		return $this;
	}
	
	function addValutazioneElemento(ValutazioneElementoChecklistPagamento $valutazione_elemento): self {
		$this->valutazioni_elementi->add($valutazione_elemento);
		$valutazione_elemento->setValutazioneChecklist($this);

		return $this;
	}
	
	function getPunteggio(): ?int {
		return $this->punteggio;
	}

	function setPunteggio(?int $punteggio): self {
		$this->punteggio = $punteggio;

		return $this;
	}

	function getDataValidazione(): ?\DateTime {
		return $this->data_validazione;
	}

	function setDataValidazione(?\DateTime $data_validazione) {
		$this->data_validazione = $data_validazione;
	}
    
    function getPagamento(): Pagamento {
        return $this->pagamento;
    }

    function setPagamento(Pagamento $pagamento) {
        $this->pagamento = $pagamento;
        return $this;
    }

	public function getRichiesta(): ?Richiesta {
		return $this->getPagamento()->getRichiesta();
	}
		
	public function __toString() {
		$descrizione = $this->getChecklist()->getNome();
		
		return $descrizione;
	}
	
	public function getDescrizioneValutazione() {
		$descrizione = $this->getChecklist()->getNome();
		
		if (!is_null($this->punteggio)) {
			$descrizione .= " / Punteggio: ".$this->getPunteggio();
		}
		
		return $descrizione;		
	}

	public function getProcedura() {
		$procedura = $this->pagamento->getProcedura();
		
		return $procedura;
	}
	
	public function isValidata(): bool {
		return $this->validata == true;
	}
	
	public function isAmmissibile(): bool {
		return $this->isValidata() && $this->ammissibile == true;
	}
	
	public function getStoricoAzioni(): Collection {
		return $this->storicoAzioni;
	}

	public function setStoricoAzioni(Collection $storicoAzioni): self {
		$this->storicoAzioni = $storicoAzioni;

		return $this;
	}

	public function addStoricoAzione(StoricoAzioniValutazioneChecklistPagamento $storicoAzione) {
		$storicoAzione->setValutazioneChecklistPagamento($this);
		return $this->storicoAzioni->add($storicoAzione);		
	}
	
	public function getDocumentiChecklist(): Collection {
		return $this->documenti_checklist;
	}

	public function setDocumentiChecklist(Collection $documenti_checklist) {
		$this->documenti_checklist = $documenti_checklist;
	}
	
	public function addDocumentoChecklist(DocumentoChecklistPagamento $documento_checklist) {
		$documento_checklist->setValutazioneChecklistPagamento($this);
		return $this->documenti_checklist->add($documento_checklist);		
	}
	
	public function removeDocumentoChecklist(DocumentoChecklistPagamento $documentoChecklist) {
		return $this->documenti_checklist->removeElement($documentoChecklist);
	}

}
