<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="istruttorie_oggetti_pagamento")
 */
class IstruttoriaOggettoPagamento extends \BaseBundle\Entity\EntityLoggabile {
	
	const COMPLETA = 'Completa';
	const INTEGRAZIONE = 'Integrazione';
	const INCOMPLETA = 'Incompleta';

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $nota_integrazione;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $stato_valutazione;
	
	public function getId() {
		return $this->id;
	}

	public function getNotaIntegrazione() {
		return $this->nota_integrazione;
	}

	public function getStatoValutazione() {
		return $this->stato_valutazione;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setNotaIntegrazione($nota_integrazione) {
		$this->nota_integrazione = $nota_integrazione;
	}

	public function setStatoValutazione($stato_valutazione) {
		$this->stato_valutazione = $stato_valutazione;
	}

	public function isCompleta(){
		return $this->stato_valutazione == self::COMPLETA;
	}
	
	public function isIntegrazione(){
		return $this->stato_valutazione == self::INTEGRAZIONE;
	}
	
	public function isIncompleta(){
		return $this->stato_valutazione == self::INCOMPLETA;
	}

	public function __clone(){
		if($this->id){
			$this->id = NULL;
		}
	}
}
