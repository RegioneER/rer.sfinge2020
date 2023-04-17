<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Entity;

/**
 * Description of Provincia.
 *
 * @author lfontana
 */
class Provincia
{
    /**
     * @var string
     */
    public $regione;

    /**
     * @var string
     */
    public $codice;

    /**
     * @var string
     */
    public $descrizione;

    public function getRegione()
    {
        return $this->regione;
    }

    public function getCodice()
    {
        return $this->codice;
    }

    public function getDescrizione()
    {
        return $this->descrizione;
    }

    public function setRegione($regione)
    {
        $this->regione = $regione;
    }

    public function setCodice($codice)
    {
        $this->codice = $codice;
    }

    public function setDescrizione($descrizione)
    {
        $this->descrizione = $descrizione;
    }

    public function __construct($codice, $descrizione, $regione)
    {
        $this->codice = $codice;
        $this->descrizione = $descrizione;
        $this->regione = $regione;
    }

    public function __toString()
    {
        return $this->descrizione;
    }
}
