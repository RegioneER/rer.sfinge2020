<?php

namespace AttuazioneControlloBundle\Form\Entity\Controlli;

use BaseBundle\Service\AttributiRicerca;

class RicercaControlli extends AttributiRicerca {

    protected $denominazione;
    protected $codice_fiscale;
    protected $procedura;
    private $atto;
    protected $utente;
    protected $completata;
    protected $protocollo;
    protected $comune;
    protected $numeroElementiPerPagina = null;
    protected $campione = null;
    protected $tipo_controllo = null;
        
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

    public function getAtto() {
        return $this->atto;
    }

    public function setAtto($atto) {
        $this->atto = $atto;
    }

    public function getType() {
        return "AttuazioneControlloBundle\Form\Controlli\RicercaControlliType";
    }

    public function getNomeRepository() {
        return "AttuazioneControlloBundle:Controlli\ControlloProgetto";
    }

    public function getNomeMetodoRepository() {
        return "getControlli";
    }

    public function getNumeroElementiPerPagina() {
        return $this->numeroElementiPerPagina;
    }

    public function getNomeParametroPagina() {
        return "page";
    }

    function mergeFreshData($freshData) {
        $this->setUtente($freshData->getUtente());
    }

    public function getComune() {
        return $this->comune;
    }

    public function setComune($comune) {
        $this->comune = $comune;
    }

    public function getCampione() {
        return $this->campione;
    }

    public function setCampione($campione): void {
        $this->campione = $campione;
    }
    
    public function getTipoControllo() {
        return $this->tipo_controllo;
    }

    public function setTipoControllo($tipo_controllo): void {
        $this->tipo_controllo = $tipo_controllo;
    }



}
