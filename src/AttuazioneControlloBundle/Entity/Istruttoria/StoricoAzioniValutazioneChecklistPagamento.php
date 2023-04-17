<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;


/**
 *
 * @ORM\Table(name = "storico_azioni_checklist_pagamento")
 * @ORM\Entity()
 */
class StoricoAzioniValutazioneChecklistPagamento {
	
	const EVENTO_VALIDATA = 'VALIDATA';
	const EVENTO_VALIDATA_LIQUIDABILE = 'VALIDATA_LIQUIDABILE';
	const EVENTO_VALIDATA_LIQUIDABILE_CONTROLLI = 'VALIDATA_LIQUIDABILE_CONTROLLI';
	const EVENTO_VALIDATA_NON_LIQUIDABILE = 'VALIDATA_NON_LIQUIDABILE';
	const EVENTO_INVALIDATA = 'INVALIDATA';
	const EVENTO_SALVATA = 'SALVATA';

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 *
	 * @ORM\Column(name="data", type="datetime", nullable=false)
	 */
	private $data;
	
	/**
	 * @ORM\Column(name="evento", type="string", nullable=false)
	 */
	private $evento;
	
	/**
	 * @ORM\Column(name="nota", type="text", nullable=true)
	 */
	private $nota;
			
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento", inversedBy="storicoAzioni")
	 * @ORM\JoinColumn(name = "valutazione_checklist_pagamento_id", referencedColumnName = "id", nullable = false)
	 */
	private $valutazioneChecklistPagamento;
	
	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente")
	 * @ORM\JoinColumn(nullable=false)
	 *
	 */
	private $valutatore;
		
	public function __construct() {

	}

	public function getId() {
		return $this->id;
	}

	public function getData() {
		return $this->data;
	}

	public function getEvento() {
		return $this->evento;
	}

	public function getNota() {
		return $this->nota;
	}

	public function getValutazioneChecklistPagamento() {
		return $this->valutazioneChecklistPagamento;
	}

	public function getValutatore() {
		return $this->valutatore;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function setEvento($evento) {
		$this->evento = $evento;
	}

	public function setNota($nota) {
		$this->nota = $nota;
	}

	public function setValutazioneChecklistPagamento($valutazioneChecklistPagamento) {
		$this->valutazioneChecklistPagamento = $valutazioneChecklistPagamento;
	}

	public function setValutatore($valutatore) {
		$this->valutatore = $valutatore;
	}

	public function getDescrizioneEvento(){
		
		$descrizioni = array(
			self::EVENTO_VALIDATA => 'validata',
			self::EVENTO_INVALIDATA => 'invalidata',
			self::EVENTO_VALIDATA_LIQUIDABILE => 'validata come liquidabile',
			self::EVENTO_VALIDATA_LIQUIDABILE_CONTROLLI => 'validata come liquidabile per controllo in loco',
			self::EVENTO_VALIDATA_NON_LIQUIDABILE => 'validata come non liquidabile',
			self::EVENTO_SALVATA => 'salvata'		
		);
		
		return $descrizioni[$this->evento];
	}
}
	
	
