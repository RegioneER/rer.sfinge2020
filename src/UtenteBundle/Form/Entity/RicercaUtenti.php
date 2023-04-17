<?php

namespace UtenteBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaUtenti extends AttributiRicerca  {

	private $username;
	private $email;
	private $ruoli;
	private $attivo;
	private $id;
	private $idPersona;
	private $ruoliEsclusi;
	
	function getUsername() {
		return $this->username;
	}

	function getEmail() {
		return $this->email;
	}

	function getRuoli() {
		return $this->ruoli;
	}

	function getAttivo() {
		return $this->attivo;
	}

	function getId() {
		return $this->id;
	}

	function getIdPersona() {
		return $this->idPersona;
	}

	function setUsername($username) {
		$this->username = $username;
	}

	function setEmail($email) {
		$this->email = $email;
	}

	function setRuoli($ruoli) {
		$this->ruoli = $ruoli;
	}

	function setAttivo($attivo) {
		$this->attivo = $attivo;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setIdPersona($idPersona) {
		$this->idPersona = $idPersona;
	}

	public function getRuoliEsclusi() {
		return $this->ruoliEsclusi;
	}

	public function setRuoliEsclusi($ruoliEsclusi) {
		$this->ruoliEsclusi = $ruoliEsclusi;
	}

		public function getType()
	{
		return "UtenteBundle\Form\RicercaUtentiType";
	}

	public function getNomeRepository()
	{
		return "SfingeBundle:Utente";
	}

	public function getNomeMetodoRepository()
	{
		return "cercaUtenti";
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
