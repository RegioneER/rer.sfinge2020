<?php

namespace AttuazioneControlloBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaAttuazione extends AttributiRicerca
{
	protected $id;
	
	protected $asse;
	
    protected $denominazione;

    protected $codice_fiscale;

    protected $procedura;
	
	protected $utente;
	
	protected $protocollo;
	
	protected $cup;
    
    protected $finestra_temporale;
	
	protected $numeroElementiPerPagina=null;
        
        protected $modalita_pagamento;
                
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
	
	function getProtocollo() {
		return $this->protocollo;
	}

	function setProtocollo($protocollo) {
		$this->protocollo = $protocollo;
	}
	
	function setNumeroElementiPerPagina($numeroElementiPerPagina) {
		$this->numeroElementiPerPagina = $numeroElementiPerPagina;
	}
	
	function getId() {
		return $this->id;
	}

	function getAsse() {
		return $this->asse;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setAsse($asse) {
		$this->asse = $asse;
	}
	
	public function getCup() {
		return $this->cup;
	}

	public function setCup($cup) {
		$this->cup = $cup;
	}
    
    public function getFinestraTemporale() {
        return $this->finestra_temporale;
    }

    public function setFinestraTemporale($finestra_temporale) {
        $this->finestra_temporale = $finestra_temporale;
    }
    
    function getModalitaPagamento() {
        return $this->modalita_pagamento;
    }

    function setModalitaPagamento($modalita_pagamento) {
        $this->modalita_pagamento = $modalita_pagamento;
    }

    public function getType()
    {
        return "AttuazioneControlloBundle\Form\RicercaAttuazioneType";
    }

    public function getNomeRepository()
    {
        return "RichiesteBundle:Richiesta";
    }

    public function getNomeMetodoRepository()
    {
        return "getRichiesteInAttuazione";
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