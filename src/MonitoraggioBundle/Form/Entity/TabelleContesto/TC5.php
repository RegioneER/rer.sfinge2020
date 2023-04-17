<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Entity\TabelleContesto;

use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;

/**
 * Description of TC5
 *
 * @author lfontana
 */
class TC5 extends Base {
     /**
      *
      * @var string
      * @ViewElenco( ordine = 1, titolo="Tipo operazione" )
      * @RicercaFormType( ordine = 1, type = "text", label = "Tipo operazione")
      */
     protected $tipo_operazione;


    /**
      *
      * @var string
      * @ViewElenco( ordine = 2, titolo="Codice natura CUP" )
      * @RicercaFormType( ordine = 2, type = "text", label = "Codice natura CUP")
      */
    protected $codice_natura_cup;


    /**
      *
      * @var string
      * @ViewElenco( ordine = 3, titolo="Descrizione codice natura CUP" )
      * @RicercaFormType( ordine = 3, type = "text", label = "Descrizione codice natura CUP")
      */
    protected $descrizione_natura_cup;


     /**
      *
      * @var string
      * @ViewElenco( ordine = 4, titolo="Codice tipologia CUP" )
      * @RicercaFormType( ordine = 4, type = "text", label = "Codice tipologia CUP")
      */
    protected $codice_tipologia_cup;


    /**
      *
      * @var string
      * @ViewElenco( ordine = 5, titolo="Descrizione tipologia CUP" )
      * @RicercaFormType( ordine = 5, type = "text", label = "Descrizione tipologia CUP")
      */
    protected $descrizione_tipologia_cup;


    /**
      *
      * @var string
      * @ViewElenco( ordine = 6, titolo="Origine del dato" )
      * @RicercaFormType( ordine = 6, type = "text", label = "Origine del dato")
      */
    protected $origine_dato;

    public function getTipoOperazione() {
        return $this->tipo_operazione;
    }

    public function getCodiceNaturaCup() {
        return $this->codice_natura_cup;
    }

    public function getDescrizioneNaturaCup() {
        return $this->descrizione_natura_cup;
    }

    public function getCodiceTipologiaCup() {
        return $this->codice_tipologia_cup;
    }

    public function getDescrizioneTipologiaCup() {
        return $this->descrizione_tipologia_cup;
    }

    public function getOrigineDato() {
        return $this->origine_dato;
    }

    public function setTipoOperazione($tipo_operazione) {
        $this->tipo_operazione = $tipo_operazione;
    }

    public function setCodiceNaturaCup($codice_natura_cup) {
        $this->codice_natura_cup = $codice_natura_cup;
    }

    public function setDescrizioneNaturaCup($descrizione_natura_cup) {
        $this->descrizione_natura_cup = $descrizione_natura_cup;
    }

    public function setCodiceTipologiaCup($codice_tipologia_cup) {
        $this->codice_tipologia_cup = $codice_tipologia_cup;
    }

    public function setDescrizioneTipologiaCup($descrizione_tipologia_cup) {
        $this->descrizione_tipologia_cup = $descrizione_tipologia_cup;
    }

    public function setOrigineDato($origine_dato) {
        $this->origine_dato = $origine_dato;
    }


}
