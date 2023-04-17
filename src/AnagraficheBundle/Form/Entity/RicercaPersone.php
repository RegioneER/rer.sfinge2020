<?php

namespace AnagraficheBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaPersone extends AttributiRicerca {

	protected $utente;
	protected $nome;
	protected $cognome;
	protected $email_principale;
	protected $codice_fiscale;

	function getUtente() {
		return $this->utente;
	}

	function setUtente($utente) {
		$this->utente = $utente;
	}

		/**
	 * @return mixed
	 */
	public function getCodiceFiscale() {
		return strtoupper($this->codice_fiscale);
	}

	/**
	 * @param mixed $codice_fiscale
	 */
	public function setCodiceFiscale($codice_fiscale) {
		$this->codice_fiscale = $codice_fiscale;
	}

	/**
	 * @return mixed
	 */
	public function getCognome() {
		return $this->cognome;
	}

	/**
	 * @param mixed $cognome
	 */
	public function setCognome($cognome) {
		$this->cognome = $cognome;
	}

	/**
	 * @return mixed
	 */
	public function getNome() {
		return $this->nome;
	}

	/**
	 * @param mixed $nome
	 */
	public function setNome($nome) {
		$this->nome = $nome;
	}

	/**
	 * @return mixed
	 */
	public function getEmailPrincipale()
	{
		return $this->email_principale;
	}

	/**
	 * @param mixed $email_principale
	 */
	public function setEmailPrincipale($email_principale)
	{
		$this->email_principale = $email_principale;
	}


	public function getType()
	{
		return "AnagraficheBundle\Form\RicercaPersoneType";
	}

	public function getNomeRepository()
	{
		return "AnagraficheBundle:Persona";
	}

	public function getNomeMetodoRepository()
	{
		return "cercaPersone";
	}

	public function getNumeroElementiPerPagina()
	{
		return null;
	}

	public function getNomeParametroPagina()
	{
		return "page";
	}



}
