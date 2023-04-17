<?php

namespace AnagraficheBundle\Form\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class PersonaPA  {

	/**
	 * @Assert\NotBlank()
	 * @Assert\Length(min=2, max=32)
	 */
	protected $nome;
	
	/** 
	 * @Assert\NotBlank()
	 * @Assert\Length(min=2, max=32)
	 */
	protected $cognome;
	
	/**
	 * @Assert\NotBlank(message="Specificare l'indirizzo email")
	 * @Assert\Length(max = "128")
	 */
	protected $email_principale;
	
	/**
	 * @Assert\NotBlank(message="Specificare il numero di telefono o rimuoverlo (un telefono è obbligatorio)")
	 * @Assert\Length(min = "8", max = "20")
	 * @Assert\Regex(pattern="/^[\d]+$/", message="Il telefono può contenere solo cifre")
	 */
	protected $telefono_principale;

	
	function getNome() {
		return $this->nome;
	}

	function getCognome() {
		return $this->cognome;
	}

	function getEmailPrincipale() {
		return $this->email_principale;
	}

	function getTelefonoPrincipale() {
		return $this->telefono_principale;
	}

	function setNome($nome) {
		$this->nome = $nome;
	}

	function setCognome($cognome) {
		$this->cognome = $cognome;
	}

	function setEmailPrincipale($email_principale) {
		$this->email_principale = $email_principale;
	}

	function setTelefonoPrincipale($telefono_principale) {
		$this->telefono_principale = $telefono_principale;
	}

}
