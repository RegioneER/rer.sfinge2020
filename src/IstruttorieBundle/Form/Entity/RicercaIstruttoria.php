<?php

namespace IstruttorieBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaIstruttoria extends AttributiRicerca
{

    protected $denominazione;

    protected $codice_fiscale;

    protected $procedura;
	
	protected $utente;
	
	protected $completata;
	
	protected $protocollo;
	
	protected $cup;
	
	protected $numeroElementiPerPagina=null;
	
	protected $finestraTemporale;	
	
	protected $metodoRicerca="getRichiesteInIstruttoria";

	protected $prorogaGestita;

	protected $id;

    protected $istruttore_corrente;

    protected $istruttori = array();


	public function getId()
	{
		return $this->id;
	}
	
	public function setId($id): self
	{
		$this->id = $id;

		return $this;
	}
	
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
	
	
	function getCup() {
		return $this->cup;
	}

	function setCup($cup) {
		$this->cup = $cup;
	}

	function getProrogaGestita() {
		return $this->prorogaGestita;
	}

	function setProrogaGestita($prorogaGestita) {
		$this->prorogaGestita = $prorogaGestita;
	}

	function setNumeroElementiPerPagina($numeroElementiPerPagina) {
		$this->numeroElementiPerPagina = $numeroElementiPerPagina;
	}

			
	function getMetodoRicerca() {
		return $this->metodoRicerca;
	}

	function setMetodoRicerca($metodoRicerca) {
		$this->metodoRicerca = $metodoRicerca;
	}

		
    public function getType()
    {
        return "IstruttorieBundle\Form\RicercaIstruttoriaType";
    }

    public function getNomeRepository()
    {
        return "RichiesteBundle:Richiesta";
    }

    public function getNomeMetodoRepository()
    {
        return $this->getMetodoRicerca();
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
        $this->setIstruttori($freshData->getIstruttori());
        if(!is_null($freshData->getIstruttoreCorrente())){
            $this->setIstruttoreCorrente($freshData->getIstruttoreCorrente());
        }
	}

	public function getFinestraTemporale() {
		return $this->finestraTemporale;
	}

	public function setFinestraTemporale($finestraTemporale) {
		$this->finestraTemporale = $finestraTemporale;
	}

    public function getIstruttoreCorrente() {
        return $this->istruttore_corrente;
    }

    public function setIstruttoreCorrente($istruttore_corrente) {
        $this->istruttore_corrente = $istruttore_corrente;
    }

    public function getIstruttori() {
        return $this->istruttori;
    }

    public function setIstruttori($istruttori) {
        $this->istruttori = $istruttori;
    }
}
