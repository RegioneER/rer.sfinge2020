<?php

namespace AnagraficheBundle\Form\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class AssociaPersonaUtente  {

	/**
	 * @Assert\NotNull()
	 */
	protected $utente;
	
	/**
	 * @Assert\NotNull()
	 */
	protected $persona;
	
	public function getUtente() {
		return $this->utente;
	}

	public function getPersona() {
		return $this->persona;
	}

	public function setUtente($utente) {
		$this->utente = $utente;
	}

	public function setPersona($persona) {
		$this->persona = $persona;
	}



}
