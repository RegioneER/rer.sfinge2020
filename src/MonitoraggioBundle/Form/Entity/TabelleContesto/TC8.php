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
 * Description of TC8
 *
 * @author lfontana
 */
class TC8 extends Base{
    
    /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice gramde progetto" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice progetto")
     */
    protected $grande_progetto;

    /**
     * @ViewElenco( ordine = 2, titolo="Descrizione grande progetto" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione progetto")
     */
    protected $descrizione_grande_progetto;

 
    protected $cod_programma;

    
    public function getGrandeProgetto() {
        return $this->grande_progetto;
    }

    public function getDescrizioneGrandeProgetto() {
        return $this->descrizione_grande_progetto;
    }

    public function getCodProgramma() {
        return $this->cod_programma;
    }

    public function setGrandeProgetto($grande_progetto) {
        $this->grande_progetto = $grande_progetto;
    }

    public function setDescrizioneGrandeProgetto($descrizione_grande_progetto) {
        $this->descrizione_grande_progetto = $descrizione_grande_progetto;
    }

    public function setCodProgramma($cod_programma) {
        $this->cod_programma = $cod_programma;
    }


}
