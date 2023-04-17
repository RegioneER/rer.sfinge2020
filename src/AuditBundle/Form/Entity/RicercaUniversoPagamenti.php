<?php

namespace AuditBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaUniversoPagamenti extends AttributiRicerca
{
	protected $id;
	
	protected $asse;
	
    protected $denominazione;

    protected $codice_fiscale;

    protected $procedura;
	
	protected $utente;
	
	protected $protocollo;
    
    protected $titolo_progetto;
    
    protected $fase;
    
    protected $certificazione;
    
    protected $totale_certificato;

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
    
    public function getTitoloProgetto() {
        return $this->titolo_progetto;
    }

    public function setTitoloProgetto($titolo_progetto) {
        $this->titolo_progetto = $titolo_progetto;
        return $this;
    }
    
    public function getFase() {
        return $this->fase;
    }

    public function setFase($fase) {
        $this->fase = $fase;
        return $this;
    }
    
    public function getCertificazione() {
        return $this->certificazione;
    }

    public function setCertificazione($certificazione) {
        $this->certificazione = $certificazione;
        return $this;
    }
    
    public function getTotaleCertificato() {
        return $this->totale_certificato;
    }

    public function setTotaleCertificato($totale_certificato) {
        $this->totale_certificato = $totale_certificato;
    }
  
    public function getType()
    {
        return "AuditBundle\Form\RicercaUniversoPagamentiType";
    }

    public function getNomeRepository()
    {
        return "AttuazioneControlloBundle:Pagamento";
    }

    public function getNomeMetodoRepository()
    {
        return "getPagamentiInUniverso";
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