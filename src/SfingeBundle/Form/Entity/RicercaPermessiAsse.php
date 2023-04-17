<?php

namespace SfingeBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaPermessiAsse extends AttributiRicerca {

	private $utente;

	private $asse;

	function getUtente() {
		return $this->utente;
	}

	function setUtente($utente) {
		$this->utente = $utente;
	}

	function getAsse() {
		return $this->asse;
	}

	function setAsse($asse) {
		$this->asse = $asse;
	}
	
	public function getType()
	{
		return "SfingeBundle\Form\RicercaPermessiAsseType";
	}

	public function getNomeRepository()
	{
		return "SfingeBundle:PermessiAsse";
	}

	public function getNomeMetodoRepository()
	{
		return "cercaPermessiAsse";
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
