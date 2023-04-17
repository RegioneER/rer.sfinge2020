<?php

namespace SegnalazioniBundle\Form\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use SegnalazioniBundle\Validator\Constraints\ValidaMantis;

/**
 * @ValidaMantis
 */
class Mantis {
	
	/**
	 * @Assert\NotBlank()
	 */
	protected $username;
	
	protected $numero_bando;
	
	protected $processo;
	
	protected $protocollo_progetto;
	
	/**
	 * @Assert\NotBlank()
	 */
	protected $oggetto;
	
	/**
	 * @Assert\NotBlank()
	 */
	protected $descrizione;
	
	protected $file;
	protected $contatto_telefonico;
	
	/**
	 * @Assert\NotBlank()
	 */
	protected $password;
	

	protected $ripeti_password;
	
	protected $obbligatorio;
	
	function getObbligatorio() {
		return $this->obbligatorio;
	}

	function setObbligatorio($obbligatorio) {
		$this->obbligatorio = $obbligatorio;
	}

		
	function getUsername() {
		return $this->username;
	}

	function getNumeroBando() {
		return $this->numero_bando;
	}

	function getProcesso() {
		return $this->processo;
	}

	function getProtocolloProgetto() {
		return $this->protocollo_progetto;
	}

	function getOggetto() {
		return $this->oggetto;
	}

	function getDescrizione() {
		return $this->descrizione;
	}

	function getFile() {
		return $this->file;
	}

	function getContattoTelefonico() {
		return $this->contatto_telefonico;
	}

	function getPassword() {
		return $this->password;
	}

	function getRipetiPassword() {
		return $this->ripeti_password;
	}

	function setUsername($username) {
		$this->username = $username;
	}

	function setNumeroBando($numero_bando) {
		$this->numero_bando = $numero_bando;
	}

	function setProcesso($processo) {
		$this->processo = $processo;
	}

	function setProtocolloProgetto($protocollo_progetto) {
		$this->protocollo_progetto = $protocollo_progetto;
	}

	function setOggetto($oggetto) {
		$this->oggetto = $oggetto;
	}

	function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;
	}

	function setFile($file) {
		$this->file = $file;
	}

	function setContattoTelefonico($contatto_telefonico) {
		$this->contatto_telefonico = $contatto_telefonico;
	}

	function setPassword($password) {
		$this->password = $password;
	}

	function setRipetiPassword($ripeti_password) {
		$this->ripeti_password = $ripeti_password;
	}


	
}

