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
class TC43 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Codice indicatore")
      * @ViewElenco( ordine = 1, titolo="Codice indicatore" )
     */
  protected $cod_indicatore;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 2, type = "text", label = "Codice indicatore risultato")
      * @ViewElenco( ordine = 2, titolo="Codice indicatore risultato" )
     */
    protected $cod_indicatore_ris;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 3, type = "text", label = "Descrizione indicatore")
      * @ViewElenco( ordine = 3, titolo="Descrizione indicatore" )
     */
    protected $descrizione_indicatore;

    /**
     *
     * @var \MonitoraggioBundle\Entity\TC4Programma
        * @RicercaFormType( ordine = 4, type = "entity", label = "Programma", options={"class": "MonitoraggioBundle\Entity\TC4Programma"})
      * @ViewElenco( ordine = 4, titolo="Programma" )
     */
    protected $programma;

   /**
     *
     * @var string
        * @RicercaFormType( ordine = 5, type = "text", label = "Fonte del dato")
      * @ViewElenco( ordine = 5, titolo="Fonte" )
     */
    protected $fonte_dato;

    
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
    public function getCodIndicatoreRis()
    {
        return $this->cod_indicatore_ris;
    }

    /**
     * @param mixed $cod_indicatore_ris
     */
    public function setCodIndicatoreRis($cod_indicatore_ris)
    {
        $this->cod_indicatore_ris = $cod_indicatore_ris;
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
    public function getProgramma()
    {
        return $this->programma;
    }

    /**
     * @param mixed $cod_programma
     */
    public function setProgramma($cod_programma)
    {
        $this->programma = $cod_programma;
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
