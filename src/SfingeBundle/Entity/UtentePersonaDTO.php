<?php

namespace SfingeBundle\Entity;

class UtentePersonaDTO
{
	public $id;
	public $nome;
	public $cognome;
	
	public function __construct($id, $nome, $cognome) {
		$this->id = $id;
		$this->nome = $nome;
		$this->cognome = $cognome;
	}
	
	public function __toString() {
		return $this->nome . " " .$this->cognome;
	}
	
	public function getId() {
		return $this->id;
	}

	public function getNome() {
		return $this->nome;
	}

	public function getCognome() {
		return $this->cognome;
	}



}
