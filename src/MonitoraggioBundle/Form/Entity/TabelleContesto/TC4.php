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
 * Description of TC4
 *
 * @author lfontana
 */
class TC4 extends Base{
    
     /**
      *
      * @var string
      * @ViewElenco( ordine = 1, titolo="Codice" )
      * @RicercaFormType( ordine = 1, type = "text", label = "Codice")
      */
     protected $cod_programma;


    /**
     * @var string
      * @ViewElenco( ordine = 2, titolo="Descrizione" )
      * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione")
     */
    protected $descrizione_programma;


    /**
      * @var string
      * @ViewElenco( ordine = 3, titolo="Fondo di riferimento" )
      * @RicercaFormType( ordine = 3, type = "text", label = "Fondo di riferimento")
     */
    protected $fondo;


    /**
      * @var string
      * @ViewElenco( ordine = 4, titolo="Codice tipologia" )
      * @RicercaFormType( ordine = 4, type = "text", label = "Codice tipologia")
     */
    protected $codice_tipologia_programma;
    
    public function getCodProgramma() {
        return $this->cod_programma;
    }

    public function getDescrizioneProgramma() {
        return $this->descrizione_programma;
    }

    public function getFondo() {
        return $this->fondo;
    }

    public function getCodiceTipologiaProgramma() {
        return $this->codice_tipologia_programma;
    }

    public function setCodProgramma($cod_programma) {
        $this->cod_programma = $cod_programma;
    }

    public function setDescrizioneProgramma($descrizione_programma) {
        $this->descrizione_programma = $descrizione_programma;
    }

    public function setFondo($fondo) {
        $this->fondo = $fondo;
    }

    public function setCodiceTipologiaProgramma($codice_tipologia_programma) {
        $this->codice_tipologia_programma = $codice_tipologia_programma;
    }


}
