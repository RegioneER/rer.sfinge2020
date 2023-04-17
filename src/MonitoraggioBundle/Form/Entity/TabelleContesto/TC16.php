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
 * Description of TC16
 *
 * @author lfontana
 */
class TC16 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice regione")
      * @ViewElenco( ordine = 1, titolo="Codice regione" )
     */
    protected $codice_regione;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Regione")
      * @ViewElenco( ordine = 2, titolo="Regione" )
     */
    protected $descrizione_regione;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 3, type = "text", label = "Codice provincia")
      * @ViewElenco( ordine = 3, titolo="Codice provincia" )
     */
    protected $codice_provincia;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 4, type = "text", label = "Provincia")
      * @ViewElenco( ordine = 4, titolo="Provincia" )
     */
    protected $descrizione_provincia;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 5, type = "text", label = "Codice comune")
      * @ViewElenco( ordine = 5, titolo="Codice comune" )
     */
    protected $codice_comune;

   /**
     *
     * @var string
     * @RicercaFormType( ordine = 6, type = "text", label = "Comune")
      * @ViewElenco( ordine = 6, titolo="Comune" )
     */
    protected $descrizione_comune;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 7, type = "text", label = "NUTS I livello")
      * @ViewElenco( ordine = 7, titolo="NUTS I livello" ,show = false )
     */
    protected $nuts_1;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 8, type = "text", label = "NUTS II livello")
      * @ViewElenco( ordine = 8, titolo="NUTS II livello" ,show = false )
     */
    protected $nuts_2;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 9, type = "text", label = "NUTS III livello")
      * @ViewElenco( ordine = 9, titolo="NUTS III livello" ,show = false)
     */
    protected $nuts_3;

    /**
     * @return mixed
     */
    public function getCodiceRegione()
    {
        return $this->codice_regione;
    }

    /**
     * @param mixed $codice_regione
     */
    public function setCodiceRegione($codice_regione)
    {
        $this->codice_regione = $codice_regione;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneRegione()
    {
        return $this->descrizione_regione;
    }

    /**
     * @param mixed $descrizione_regione
     */
    public function setDescrizioneRegione($descrizione_regione)
    {
        $this->descrizione_regione = $descrizione_regione;
    }

    /**
     * @return mixed
     */
    public function getCodiceProvincia()
    {
        return $this->codice_provincia;
    }

    /**
     * @param mixed $codice_provincia
     */
    public function setCodiceProvincia($codice_provincia)
    {
        $this->codice_provincia = $codice_provincia;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneProvincia()
    {
        return $this->descrizione_provincia;
    }

    /**
     * @param mixed $descrizione_provincia
     */
    public function setDescrizioneProvincia($descrizione_provincia)
    {
        $this->descrizione_provincia = $descrizione_provincia;
    }

    /**
     * @return mixed
     */
    public function getCodiceComune()
    {
        return $this->codice_comune;
    }

    /**
     * @param mixed $codice_comune
     */
    public function setCodiceComune($codice_comune)
    {
        $this->codice_comune = $codice_comune;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneComune()
    {
        return $this->descrizione_comune;
    }

    /**
     * @param mixed $descrizione_comune
     */
    public function setDescrizioneComune($descrizione_comune)
    {
        $this->descrizione_comune = $descrizione_comune;
    }

    /**
     * @return mixed
     */
    public function getNuts1()
    {
        return $this->nuts_1;
    }

    /**
     * @param mixed $nuts_1
     */
    public function setNuts1($nuts_1)
    {
        $this->nuts_1 = $nuts_1;
    }

    /**
     * @return mixed
     */
    public function getNuts2()
    {
        return $this->nuts_2;
    }

    /**
     * @param mixed $nuts_2
     */
    public function setNuts2($nuts_2)
    {
        $this->nuts_2 = $nuts_2;
    }

    /**
     * @return mixed
     */
    public function getNuts3()
    {
        return $this->nuts_3;
    }

    /**
     * @param mixed $nuts_3
     */
    public function setNuts3($nuts_3)
    {
        $this->nuts_3 = $nuts_3;
    }


}
