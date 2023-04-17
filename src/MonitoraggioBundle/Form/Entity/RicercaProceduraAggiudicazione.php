<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

/**
 * Description of RicercaProgetti
 *
 * @author lfontana
 */
class RicercaProceduraAggiudicazione extends AttributiRicerca{
    
    protected $numeroElementiPerPagina;
    protected $richiesta;

    private $codice;
    private $cig;
    private $motivo_assenza_cig;
    private $descrizione_procedura_aggiudicazione;
    private $tipo_procedura_aggiudicazione;
    private $importo_procedura_aggiudicazione;
    private $importo_aggiudicato;

    public function __construct($richiesta) {
        $this->setRichiesta($richiesta);
    }
    
    function getCodice() {
        return $this->codice;
    }

    function getCig() {
        return $this->cig;
    }

    function getMotivoAssenzaCig() {
        return $this->motivo_assenza_cig;
    }

    function getDescrizioneProceduraAggiudicazione() {
        return $this->descrizione_procedura_aggiudicazione;
    }

    function getTipoProceduraAggiudicazione() {
        return $this->tipo_procedura_aggiudicazione;
    }

    function getImportoProceduraAggiudicazione() {
        return $this->importo_procedura_aggiudicazione;
    }

    function getImportoAggiudicato() {
        return $this->importo_aggiudicato;
    }

    function setCodice($codice) {
        $this->codice = $codice;
    }

    function setCig($cig) {
        $this->cig = $cig;
    }

    function setMotivoAssenzaCig($motivo_assenza_cig) {
        $this->motivo_assenza_cig = $motivo_assenza_cig;
    }

    function setDescrizioneProceduraAggiudicazione($descrizione_procedura_aggiudicazione) {
        $this->descrizione_procedura_aggiudicazione = $descrizione_procedura_aggiudicazione;
    }

    function setTipoProceduraAggiudicazione($tipo_procedura_aggiudicazione) {
        $this->tipo_procedura_aggiudicazione = $tipo_procedura_aggiudicazione;
    }

    function setImportoProceduraAggiudicazione($importo_procedura_aggiudicazione) {
        $this->importo_procedura_aggiudicazione = $importo_procedura_aggiudicazione;
    }

    function setImportoAggiudicato($importo_aggiudicato) {
        $this->importo_aggiudicato = $importo_aggiudicato;
    }
    
    function getRichiesta() {
        return $this->richiesta;
    }

    function setRichiesta($richiesta) {
        $this->richiesta = $richiesta;
    }

    public function getType() {
        return "MonitoraggioBundle\Form\Ricerca\RicercaProceduraAggiudicazioneType";
    }

    public function getNomeRepository() {
        return "AttuazioneControlloBundle:ProceduraAggiudicazione";
    }

    public function getNomeMetodoRepository() {
        return "getProcedureAggiudicazione";
    }

    public function getNumeroElementiPerPagina() {
        return $this->numeroElementiPerPagina;
    }
    
    public function setNumeroElementiPerPagina($numeroElementiPerPagina) {
        $this->numeroElementiPerPagina = $numeroElementiPerPagina;
    }

    public function getNomeParametroPagina() {
        return "page";
    }

}
