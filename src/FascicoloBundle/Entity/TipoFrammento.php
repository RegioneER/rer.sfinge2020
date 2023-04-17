<?php

namespace FascicoloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of TipoFrammento
 *
 * @author abuffa
 *
 * @ORM\Entity
 * @ORM\Table(name="fascicoli_tipo_frammento")
 */
class TipoFrammento {

    /**
	 * @var integer $id
	 * 
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */	
	protected $id;
		
	/**
     * @var ArrayCollection $frammenti
     * 
	 * @ORM\OneToMany(targetEntity="Frammento", mappedBy="tipoFrammento")
	 */
	protected $frammenti;
	
	/**
	 * @var string $codice
	 * 
	 * @ORM\Column(name="codice", type="string", length=255, nullable=false)
	 */
	protected $codice;
	
	/**
	 * @var string $nome
	 * 
	 * @ORM\Column(name="nome", type="string", length=255, nullable=false)
	 */
	protected $nome;
	
	/**
	 * @var boolean $campi
	 * 
	 * @ORM\Column(name="campi", type="boolean", nullable=true)
	 */
	protected $campi;
	
	/**
	 * @var boolean $sottoPagine
	 * 
	 * @ORM\Column(name="sotto_pagine", type="boolean", nullable=true)
	 */
	protected $sottoPagine;	
	
	function getId() {
		return $this->id;
	}

	public function getFrammenti() {
		return $this->frammenti;
	}

	function getCodice() {
		return $this->codice;
	}

	function getNome() {
		return $this->nome;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setFrammenti($frammenti) {
		$this->frammenti = $frammenti;
	}

	function setCodice($codice) {
		$this->codice = $codice;
	}

	function setNome($nome) {
		$this->nome = $nome;
	}
	
	public function getCampi() {
		return $this->campi;
	}

	public function getSottoPagine() {
		return $this->sottoPagine;
	}

	public function setCampi($campi) {
		$this->campi = $campi;
	}

	public function setSottoPagine($sottoPagine) {
		$this->sottoPagine = $sottoPagine;
	}

	public function __construct() {
		$this->frammenti = new ArrayCollection();
	}
	
	public function __toString() {
		if ($this->getNome()) {
			return $this->getNome();
		} else {
			return "".$this->getId();
		}
	}

}
