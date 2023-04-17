<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneElementoChecklistPagamentoRepository")
 * @ORM\Table(name="valutazioni_elementi_checklist_pagamenti")
 * 
 */
class ValutazioneElementoChecklistPagamento {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ElementoChecklistPagamento", inversedBy="valutazioni")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $elemento;		
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $valore;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $valore_raw;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $commento;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento", inversedBy="valutazioni_elementi")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $valutazione_checklist;		
	
	function getId() {
		return $this->id;
	}

	function getElemento(): ?ElementoChecklistPagamento {
		return $this->elemento;
	}

	function getValore() {
		return $this->valore;
	}

	function getValoreRaw() {
		return $this->valore_raw;
	}

	function getCommento() {
		return $this->commento;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setElemento($elemento) {
		$this->elemento = $elemento;
	}

	function setValore($valore) {
		$this->valore = $valore;
	}

	function setValoreRaw($valore_raw) {
		$this->valore_raw = $valore_raw;
	}

	function setCommento($commento) {
		$this->commento = $commento;
	}

	function getValutazioneChecklist() {
		return $this->valutazione_checklist;
	}

	function setValutazioneChecklist($valutazione_checklist) {
		$this->valutazione_checklist = $valutazione_checklist;
	}

	
	/**
	 * @Assert\Callback
	 */
	public function validazioneTipo(\Symfony\Component\Validator\Context\ExecutionContextInterface $context)
	{
		if ($this->getElemento()->getTipo() == "integer" && !preg_match("/^(-?\d+|\d*)$/", $this->getValore())) {
			$context->buildViolation('Questo valore deve essere un numero intero')
					->atPath('valore')
					->addViolation();
		} else if (in_array($this->getElemento()->getTipo(), array("text", "textarea")) 
				&& !is_null($this->getElemento()->getLunghezzaMassima())
				&& !is_null($this->getValore())		
				&& mb_strlen($this->getValore()) > $this->getElemento()->getLunghezzaMassima()) {
			$context->buildViolation("Questo valore è troppo lungo. Dovrebbe essere al massimo di {$this->getElemento()->getLunghezzaMassima()} caratteri. Hai inserito ". mb_strlen($this->getValore()) ." caratteri")
					->atPath('valore')
					->addViolation();
		}		
	}	
}
