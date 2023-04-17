<?php

namespace CertificazioniBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaDecertificazioni extends AttributiRicerca {

	protected $procedura;
	protected $asse;
	protected $cup;
	protected $id_pagamento;
	protected $beneficiario;
	protected $id_operazione;
	protected $numeroElementiPerPagina = null;

	function getProcedura() {
		return $this->procedura;
	}

	function getAsse() {
		return $this->asse;
	}

	function setProcedura($procedura) {
		$this->procedura = $procedura;
		return $this;
	}

	function setAsse($asse) {
		$this->asse = $asse;
		return $this;
	}

	public function getIdPagamento() {
		return $this->id_pagamento;
	}

	public function getBeneficiario() {
		return $this->beneficiario;
	}

	public function setIdPagamento($id_pagamento) {
		$this->id_pagamento = $id_pagamento;
	}

	public function setBeneficiario($beneficiario) {
		$this->beneficiario = $beneficiario;
	}

	public function getCup() {
		return $this->cup;
	}

	public function setCup($cup) {
		$this->cup = $cup;
	}

	function setNumeroElementiPerPagina($numeroElementiPerPagina) {
		$this->numeroElementiPerPagina = $numeroElementiPerPagina;
	}

	public function getNomeRepository() {
		return "CertificazioniBundle:CompensazionePagamento";
	}

	public function getNumeroElementiPerPagina() {
		return $this->numeroElementiPerPagina;
	}

	public function getNomeParametroPagina() {
		return "page";
	}

	public function mostraNumeroElementi() {
		return false;
	}

	function getIdOperazione() {
		return $this->id_operazione;
	}

	function setIdOperazione($id_operazione) {
		$this->id_operazione = $id_operazione;
	}

	public function getType() {
		return "CertificazioniBundle\Form\RicercaCompensazioniType";
	}

	public function getNomeMetodoRepository() {
		return "getPagamentiCompensati";
	}

}
