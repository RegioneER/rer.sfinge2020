<?php

namespace MonitoraggioBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaTrasferimento extends AttributiRicerca {

    protected $numeroElementiPerPagina;
    
    private $bando;
    private $causale_trasferimento;
    private $cod_trasferimento;
    private $data_trasferimento;
    private $importo_trasferimento;
    private $soggetto;

    function getBando() {
        return $this->bando;
    }

    function getCausaleTrasferimento() {
        return $this->causale_trasferimento;
    }

    function getCodTrasferimento() {
        return $this->cod_trasferimento;
    }

    function getDataTrasferimento() {
        return $this->data_trasferimento;
    }

    function getImportoTrasferimento() {
        return $this->importo_trasferimento;
    }

    function getSoggetto() {
        return $this->soggetto;
    }

    function setBando($bando) {
        $this->bando = $bando;
    }

    function setCausaleTrasferimento($causale_trasferimento) {
        $this->causale_trasferimento = $causale_trasferimento;
    }

    function setCodTrasferimento($cod_trasferimento) {
        $this->cod_trasferimento = $cod_trasferimento;
    }

    function setDataTrasferimento($data_trasferimento) {
        $this->data_trasferimento = $data_trasferimento;
    }

    function setImportoTrasferimento($importo_trasferimento) {
        $this->importo_trasferimento = $importo_trasferimento;
    }

    function setSoggetto($soggetto) {
        $this->soggetto = $soggetto;
    }

    public function getType() {
        return "MonitoraggioBundle\Form\Ricerca\RicercaTrasferimentoType";
    }

    public function getNomeRepository() {
        return "MonitoraggioBundle:Trasferimento";
    }

    public function getNomeMetodoRepository() {
        return "getTrasferimenti";
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
