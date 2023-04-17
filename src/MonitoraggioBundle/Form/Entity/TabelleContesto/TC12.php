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
 * Description of TC11
 *
 * @author lfontana
 */
class TC12 extends Base{
    /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice tipo classificazione" )
     * @RicercaFormType( ordine = 1, type = "entity", label = "Codice tipo classificazione", options={"class": "MonitoraggioBundle\Entity\TC11TipoClassificazione"})
     */
    protected $tipo_classificazione;

     /**
     *
     * @var \MonitoraggioBundle\Entity\TC4Programma
     * @RicercaFormType( ordine = 2, type = "entity", label = "Programma", options={"class": "MonitoraggioBundle\Entity\TC4Programma"})
     * @ViewElenco( ordine = 2, titolo="Programma" )
     */
    protected $programma;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Origine Dato" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Origine dato")
     */
    protected $origine_dato;
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 4, type = "text", label = "Codice")
     * @ViewElenco( ordine = 4, titolo="Codice" )
     */
   protected $codice;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 5, type = "text", label = "Descrizione")
     * @ViewElenco( ordine = 6, titolo="Descrizione" )
     */
    protected $descrizione;
    
     
    function getTipoClassificazione() {
        return $this->tipo_classificazione;
    }

    function getProgramma() {
        return $this->programma;
    }

    function getOrigineDato() {
        return $this->origine_dato;
    }

    function getCodice() {
        return $this->codice;
    }

    function getDescrizione() {
        return $this->descrizione;
    }

    function setTipoClassificazione($tipo_classificazione) {
        $this->tipo_classificazione = $tipo_classificazione;
    }

    function setProgramma($programma) {
        $this->programma = $programma;
    }

    function setOrigineDato($origine_dato) {
        $this->origine_dato = $origine_dato;
    }

    function setCodice($codice) {
        $this->codice = $codice;
    }

    function setDescrizione($descrizione) {
        $this->descrizione = $descrizione;
    }


}
