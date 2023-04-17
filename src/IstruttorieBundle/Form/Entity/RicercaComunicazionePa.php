<?php

namespace IstruttorieBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaComunicazionePa extends AttributiRicerca {

	private $procedura;
	private $protocollo;
	private $soggetto;
	private $asse;
	private $tipo;
    private $utente;


    public function getProcedura() {
		return $this->procedura;
	}

	public function getProtocollo() {
		return $this->protocollo;
	}

	public function getSoggetto() {
		return $this->soggetto;
	}

	public function getAsse() {
		return $this->asse;
	}

	public function getTipo() {
		return $this->tipo;
	}

	public function setProcedura($procedura) {
		$this->procedura = $procedura;
	}

	public function setProtocollo($protocollo) {
		$this->protocollo = $protocollo;
	}

	public function setSoggetto($soggetto) {
		$this->soggetto = $soggetto;
	}

	public function setAsse($asse) {
		$this->asse = $asse;
	}

	public function setTipo($tipo) {
		$this->tipo = $tipo;
	}

	public function getType() {
		return "IstruttorieBundle\Form\RicercaComunicazionePaType";
	}

	public function getNomeRepository() {
		switch ($this->tipo) {
			case 'ESITO':
				return "IstruttorieBundle:ComunicazioneEsitoIstruttoria";
			case 'VARIAZIONE':
			case 'PROGETTO':
				return "IstruttorieBundle:ComunicazioneProgetto";
		}
	}

	public function getNomeMetodoRepository() {
		switch ($this->tipo) {
			case 'ESITO':
				return "getElencoComunicazioniEs";
			case 'VARIAZIONE':
				return "getElencoComunicazioniVar";
			case 'PROGETTO':
				return "getElencoComunicazioniPrg";
		}
	}

	public function getNumeroElementiPerPagina() {
		return null;
	}

	public function getNomeParametroPagina() {
		return "page";
	}

	function mergeFreshData($freshData) {
		$this->setSoggetto($freshData->getSoggetto());
	}
    
    public function getUtente() {
        return $this->utente;
    }

    public function setUtente($utente) {
        $this->utente = $utente;
    }

}
