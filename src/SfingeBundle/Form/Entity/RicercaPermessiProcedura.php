<?php

namespace SfingeBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaPermessiProcedura extends AttributiRicerca {

	private $utente;

	private $procedura;

	function getUtente() {
		return $this->utente;
	}

	function setUtente($utente) {
		$this->utente = $utente;
	}

	function getProcedura() {
		return $this->procedura;
	}

	function setProcedura($procedura) {
		$this->procedura = $procedura;
	}

	public function getType()
	{
		return "SfingeBundle\Form\RicercaPermessiProceduraType";
	}

	public function getNomeRepository()
	{
		return "SfingeBundle:PermessiProcedura";
	}

	public function getNomeMetodoRepository()
	{
		return "cercaPermessiProcedura";
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
