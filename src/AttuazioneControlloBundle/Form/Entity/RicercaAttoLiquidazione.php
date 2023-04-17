<?php


namespace AttuazioneControlloBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaAttoLiquidazione extends AttributiRicerca
{

    private $descrizione;

    private $numero;
    
    private $asse;

    private $dataAttoDa;

    private $dataAttoA;

    function getDescrizione() {
        return $this->descrizione;
    }

    function getNumero() {
        return $this->numero;
    }

    function setDescrizione($descrizione) {
        $this->descrizione = $descrizione;
        return $this;
    }

    function setNumero($numero) {
        $this->numero = $numero;
        return $this;
    }

    function getAsse() {
        return $this->asse;
    }

    function setAsse($asse) {
        $this->asse = $asse;
        return $this;
    }

    public function getDataAttoA()
    {
        return $this->dataAttoA;
    }

    public function setDataAttoA($dataAttoA)
    {
        $this->dataAttoA = $dataAttoA;
    }

    public function getDataAttoDa()
    {
        return $this->dataAttoDa;
    }

    public function setDataAttoDa($dataAttoDa)
    {
        $this->dataAttoDa = $dataAttoDa;
    }
    
    public function getType()
    {
        return "AttuazioneControlloBundle\Form\RicercaAttoLiquidazioneType";
    }

    public function getNomeRepository()
    {
        return "AttuazioneControlloBundle:AttoLiquidazione";
    }

    public function getNomeMetodoRepository()
    {
        return "cercaAttoLiquidazione";
    }

    public function getNumeroElementiPerPagina()
    {
        return null;
    }

    public function getNomeParametroPagina()
    {
        return "page";
    }

}