<?php

namespace AttuazioneControlloBundle\Form\Entity\Istruttoria;

use BaseBundle\Service\AttributiRicerca;

class RicercaVariazioni extends AttributiRicerca
{

    protected $denominazione;

    protected $codice_fiscale;

    protected $procedura;
	
	protected $utente;
	
	protected $completata;
	
	protected $protocollo;
	
	protected $numeroElementiPerPagina=null;	
		
	function getDenominazione() {
		return $this->denominazione;
	}

	function getCodiceFiscale() {
		return $this->codice_fiscale;
	}

	function getProcedura() {
		return $this->procedura;
	}

	function setDenominazione($denominazione) {
		$this->denominazione = $denominazione;
	}

	function setCodiceFiscale($codice_fiscale) {
		$this->codice_fiscale = $codice_fiscale;
	}

	function setProcedura($procedura) {
		$this->procedura = $procedura;
	}
	
	function getUtente() {
		return $this->utente;
	}

	function setUtente($utente) {
		$this->utente = $utente;
	}

	function getCompletata() {
		return $this->completata;
	}

	function setCompletata($completata) {
		$this->completata = $completata;
	}
	
	function getProtocollo() {
		return $this->protocollo;
	}

	function setProtocollo($protocollo) {
		$this->protocollo = $protocollo;
	}

	function setNumeroElementiPerPagina($numeroElementiPerPagina) {
		$this->numeroElementiPerPagina = $numeroElementiPerPagina;
	}
	
    public function getType()
    {
        return "AttuazioneControlloBundle\Form\Istruttoria\RicercaVariazioniType";
    }

    public function getNomeRepository()
    {
        return "AttuazioneControlloBundle:VariazioneRichiesta";
    }

    public function getNomeMetodoRepository()
    {
        return "getVariazioniInIstruttoria";
    }

    public function getNumeroElementiPerPagina()
    {
        return $this->numeroElementiPerPagina;
    }

    public function getNomeParametroPagina()
    {
        return "page";
    }
	
	function mergeFreshData($freshData) {
		$this->setUtente($freshData->getUtente());
	}	

}