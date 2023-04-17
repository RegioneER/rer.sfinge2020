<?php

namespace AttuazioneControlloBundle\Form\Entity\Istruttoria;

use BaseBundle\Service\AttributiRicerca;

class RicercaPagamenti extends AttributiRicerca
{

    protected $id_richiesta;
    
    protected $denominazione;

    protected $codiceFiscale;

    protected $procedura;

    protected $utente;

    protected $statoIstruttoria;

    protected $statoPagamento;

    protected $esitoProgetto;

    protected $protocollo;

    protected $asse;

    protected $numeroElementiPerPagina=null;

    protected $istruttoreCorrente;

    protected $istruttori = array();

    protected $assegnato;

    protected $certificazione;

    protected $em;
    
    protected $finestra_temporale;

    function getDenominazione() {
        return $this->denominazione;
    }

    function getCodiceFiscale() {
        return $this->codiceFiscale;
    }

    function getProcedura() {
        return $this->procedura;
    }

    function setDenominazione($denominazione) {
        $this->denominazione = $denominazione;
    }

    function setCodiceFiscale($codiceFiscale) {
        $this->codiceFiscale = $codiceFiscale;
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

    function getStatoIstruttoria() {
        return $this->statoIstruttoria;
    }

    function setStatoIstruttoria($statoIstruttoria) {
        $this->statoIstruttoria = $statoIstruttoria;
    }

    function getStatoPagamento() {
        return $this->statoPagamento;
    }

    function setStatoPagamento($statoPagamento) {
        $this->statoPagamento = $statoPagamento;
    }

    function getEsitoProgetto() {
        return $this->esitoProgetto;
    }

    function setEsitoProgetto($esitoProgetto) {
        $this->esitoProgetto = $esitoProgetto;
    }

    function getProtocollo() {
        return $this->protocollo;
    }

    function setProtocollo($protocollo) {
        $this->protocollo = $protocollo;
    }

    function getAsse() {
        return $this->asse;
    }

    function setAsse($asse) {
        $this->asse = $asse;
    }

    function setNumeroElementiPerPagina($numeroElementiPerPagina) {
        $this->numeroElementiPerPagina = $numeroElementiPerPagina;
    }

    public function getIstruttoreCorrente() {
        return $this->istruttoreCorrente;
    }

    public function setIstruttoreCorrente($istruttoreCorrente) {
        $this->istruttoreCorrente = $istruttoreCorrente;
    }

    public function getIstruttori() {
        return $this->istruttori;
    }

    public function setIstruttori($istruttori) {
        $this->istruttori = $istruttori;
    }

    public function getIdRichiesta() {
        return $this->id_richiesta;
    }

    public function setIdRichiesta($id_richiesta) {
        $this->id_richiesta = $id_richiesta;
    }

    public function getType()
    {
        return "AttuazioneControlloBundle\Form\Istruttoria\RicercaPagamentiType";
    }

    public function getNomeRepository()
    {
        return "AttuazioneControlloBundle:Pagamento";
    }

    public function getNomeMetodoRepository()
    {
        return "getPagamentiInIstruttoria";
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
        $this->setEm($freshData->getEm());
        $this->setIstruttori($freshData->getIstruttori());
        if(!is_null($freshData->getIstruttoreCorrente())){
            $this->setIstruttoreCorrente($freshData->getIstruttoreCorrente());
        }
    }

    public function getEm() {
        return $this->em;
    }

    public function setEm($em) {
        $this->em = $em;
    }

    function getAssegnato() {
        return $this->assegnato;
    }

    function setAssegnato($assegnato) {
        $this->assegnato = $assegnato;
    }

    public function getCertificazione() {
        return $this->certificazione;
    }

    public function setCertificazione($certificazione) {
        $this->certificazione = $certificazione;
    }
    
    public function getFinestraTemporale() {
        return $this->finestra_temporale;
    }

    public function setFinestraTemporale($finestra_temporale) {
        $this->finestra_temporale = $finestra_temporale;
    }
}
