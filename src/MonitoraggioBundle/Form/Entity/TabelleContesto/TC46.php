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
/**
 * Description of TC16
 *
 * @author lfontana
 */
class TC46 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Codice fase")
      * @ViewElenco( ordine = 1, titolo="Codice fase" )
     */
   protected $cod_fase;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione fase")
      * @ViewElenco( ordine = 2, titolo="Descrizione fase" )
     */
    protected $descrizione_fase;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 3, type = "text", label = "Codice natura CUP")
      * @ViewElenco( ordine = 3, titolo="Codice natura CUP" )
     */
    protected $codice_natura_cup;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 4, type = "text", label = "Descrizione natura CUP")
      * @ViewElenco( ordine = 4, titolo="Descrizione natura CUP" )
     */
    protected $descrizione_natura_cup;

    /**
     * @return mixed
     */
    public function getCodFase()
    {
        return $this->cod_fase;
    }

    /**
     * @param mixed $cod_fase
     */
    public function setCodFase($cod_fase)
    {
        $this->cod_fase = $cod_fase;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneFase()
    {
        return $this->descrizione_fase;
    }

    /**
     * @param mixed $descrizione_fase
     */
    public function setDescrizioneFase($descrizione_fase)
    {
        $this->descrizione_fase = $descrizione_fase;
    }

    /**
     * @return mixed
     */
    public function getCodiceNaturaCup()
    {
        return $this->codice_natura_cup;
    }

    /**
     * @param mixed $codice_natura_cup
     */
    public function setCodiceNaturaCup($codice_natura_cup)
    {
        $this->codice_natura_cup = $codice_natura_cup;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneNaturaCup()
    {
        return $this->descrizione_natura_cup;
    }

    /**
     * @param mixed $descrizione_natura_cup
     */
    public function setDescrizioneNaturaCup($descrizione_natura_cup)
    {
        $this->descrizione_natura_cup = $descrizione_natura_cup;
    }



}
