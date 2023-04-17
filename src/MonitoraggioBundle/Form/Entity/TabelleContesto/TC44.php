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
class TC44 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Codice indicatore output")
      * @ViewElenco( ordine = 1, titolo="Codice indicatore output" )
     */
   protected $cod_indicatore;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione indicatore output")
      * @ViewElenco( ordine = 2, titolo="Descrizione indicatore output" )
     */
    protected $descrizione_indicatore;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 3, type = "text", label = "Unità di misura")
      * @ViewElenco( ordine = 3, titolo="Unità di misura" )
     */
    protected $unita_misura;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 4, type = "text", label = "Descrizione unità di misura")
      * @ViewElenco( ordine = 4, titolo="Descrizione unità di misura" )
     */
    protected $desc_unita_misura;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 5, type = "text", label = "Flag calcolo")
      * @ViewElenco( ordine = 5, titolo="Flag calcolo" )
     */
    protected $flag_calcolo;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 6, type = "text", label = "Fonte del dato")
      * @ViewElenco( ordine = 6, titolo="Fonte" )
     */
    protected $fonte_dato;

    
    /**
     * @return mixed
     */
    public function getCodIndicatore()
    {
        return $this->cod_indicatore;
    }

    /**
     * @param mixed $cod_indicatore
     */
    public function setCodIndicatore($cod_indicatore)
    {
        $this->cod_indicatore = $cod_indicatore;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneIndicatore()
    {
        return $this->descrizione_indicatore;
    }

    /**
     * @param mixed $descrizione_indicatore
     */
    public function setDescrizioneIndicatore($descrizione_indicatore)
    {
        $this->descrizione_indicatore = $descrizione_indicatore;
    }

    /**
     * @return mixed
     */
    public function getUnitaMisura()
    {
        return $this->unita_misura;
    }

    /**
     * @param mixed $unita_misura
     */
    public function setUnitaMisura($unita_misura)
    {
        $this->unita_misura = $unita_misura;
    }

    /**
     * @return mixed
     */
    public function getDescUnitaMisura()
    {
        return $this->desc_unita_misura;
    }

    /**
     * @param mixed $desc_unita_misura
     */
    public function setDescUnitaMisura($desc_unita_misura)
    {
        $this->desc_unita_misura = $desc_unita_misura;
    }

    /**
     * @return mixed
     */
    public function getFlagCalcolo()
    {
        return $this->flag_calcolo;
    }

    /**
     * @param mixed $flag_calcolo
     */
    public function setFlagCalcolo($flag_calcolo)
    {
        $this->flag_calcolo = $flag_calcolo;
    }

    /**
     * @return mixed
     */
    public function getFonteDato()
    {
        return $this->fonte_dato;
    }

    /**
     * @param mixed $fonte_dato
     */
    public function setFonteDato($fonte_dato)
    {
        $this->fonte_dato = $fonte_dato;
    }



}
