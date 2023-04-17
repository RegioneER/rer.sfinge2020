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
 * Description of TC7
 *
 * @author lfontana
 */
class TC7 extends Base{
    
    /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice progetto" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice progetto")
     */
    protected $cod_prg_complesso;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Descrizione progetto" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione progetto")
     */
    protected $descrizione_progetto_complesso;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Codice programma" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Codice programma")
     */
    protected $cod_programma;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Codice tipologia complessità" )
     * @RicercaFormType( ordine = 4, type = "text", label = "Codice tipologia complessità")
     */
    protected $codice_tipo_complessita;


    /**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Descrizione tipologia complessità" )
     * @RicercaFormType( ordine = 5, type = "text", label = "Descrizione tipologia complessità")
     */
    protected $descrizione_tipo_complessita;
    
    public function getCodPrgComplesso() {
        return $this->cod_prg_complesso;
    }

    public function getDescrizioneProgettoComplesso() {
        return $this->descrizione_progetto_complesso;
    }

    public function getCodProgramma() {
        return $this->cod_programma;
    }

    public function getCodiceTipoComplessita() {
        return $this->codice_tipo_complessita;
    }

    public function getDescrizioneTipoComplessita() {
        return $this->descrizione_tipo_complessita;
    }

    public function setCodPrgComplesso($cod_prg_complesso) {
        $this->cod_prg_complesso = $cod_prg_complesso;
    }

    public function setDescrizioneProgettoComplesso($descrizione_progetto_complesso) {
        $this->descrizione_progetto_complesso = $descrizione_progetto_complesso;
    }

    public function setCodProgramma($cod_programma) {
        $this->cod_programma = $cod_programma;
    }

    public function setCodiceTipoComplessita($codice_tipo_complessita) {
        $this->codice_tipo_complessita = $codice_tipo_complessita;
    }

    public function setDescrizioneTipoComplessita($descrizione_tipo_complessita) {
        $this->descrizione_tipo_complessita = $descrizione_tipo_complessita;
    }


}
