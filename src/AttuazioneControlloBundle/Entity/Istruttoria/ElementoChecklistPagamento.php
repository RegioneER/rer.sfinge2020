<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="elementi_checklist_pagamenti")
 * @ORM\Entity()
 */
class ElementoChecklistPagamento
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
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\SezioneChecklistPagamento", inversedBy="elementi")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $sezione_checklist;

	/**
	 * @ORM\Column(type="text", nullable=false)
	 */
	protected $descrizione;	
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $note;
	
	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $tipo;
	
	/**
	 * @ORM\Column(type="array", name="choices", nullable=true)
	 */
	protected $choices;
	
	/**
	 * @ORM\Column(type="integer",  name="punteggio_minimo_ammissibilita", nullable=true)
	 */
	protected $punteggio_minimo_ammissibilita;
	
	/**
	 * @ORM\Column(type="integer",  name="punteggio_massimo", nullable=true)
	 */
	protected $punteggio_massimo;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneElementoChecklistPagamento", mappedBy="elemento", cascade={"persist"})
	 */		
	protected $valutazioni;
	
	/**
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $significativo;
	
	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $lunghezza_massima;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $codice;	
			
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $opzionale;    

	
	function __construct() {
		$this->valutazioni = new \Doctrine\Common\Collections\ArrayCollection();
	}
	
	function getId() {
		return $this->id;
	}

	function getSezioneChecklist() {
		return $this->sezione_checklist;
	}

	function getDescrizione() {
		return $this->descrizione;
	}

	function getNote() {
		return $this->note;
	}

	function getTipo() {
		return $this->tipo;
	}

	function getChoices() {
		return $this->choices;
	}

	function getPunteggioMinimoAmmissibilita() {
		return $this->punteggio_minimo_ammissibilita;
	}

	function getPunteggioMassimo() {
		return $this->punteggio_massimo;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setSezioneChecklist($sezione_checklist) {
		$this->sezione_checklist = $sezione_checklist;
	}

	function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;
	}

	function setNote($note) {
		$this->note = $note;
	}

	function setTipo($tipo) {
		$this->tipo = $tipo;
	}

	function setChoices($choices) {
		$this->choices = $choices;
	}

	function setPunteggioMinimoAmmissibilita($punteggio_minimo_ammissibilita) {
		$this->punteggio_minimo_ammissibilita = $punteggio_minimo_ammissibilita;
	}

	function setPunteggioMassimo($punteggio_massimo) {
		$this->punteggio_massimo = $punteggio_massimo;
	}

	function getValutazioni() {
		return $this->valutazioni;
	}

	function setValutazioni($valutazioni) {
		$this->valutazioni = $valutazioni;
	}

	function getLunghezzaMassima() {
		return $this->lunghezza_massima;
	}

	function setLunghezzaMassima($lunghezza_massima) {
		$this->lunghezza_massima = $lunghezza_massima;
	}
	
	function getSignificativo() {
		return $this->significativo;
	}

	function setSignificativo($significativo) {
		$this->significativo = $significativo;
	}
	
	function getCodice() {
		return $this->codice;
	}

	function setCodice($codice) {
		$this->codice = $codice;
	}

    public function getOpzionale() {
        return $this->opzionale;
    }

    public function setOpzionale($opzionale) {
        $this->opzionale = $opzionale;
        return $this;
    }	
}
