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
 * Description of TC12_3
 *
 * @author lfontana
 */
class TC12_8 extends Base{
    
    
     /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice classificazione tipo intervento")
      * @ViewElenco( ordine = 1, titolo="Codice classificazione tipo intervento" )
     */
      protected $cod_classificazione_ti;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Codice tipologia intervento")
     * @ViewElenco( ordine = 2, titolo="Codice tipo intervento" )
     */
    protected $cod_tipo_intervento;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 3, type = "text", label = "Descrizione tipologia intervento")
     */
    protected $desc_tipo_intervento;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 4, type = "text", label = "Codice della sottomisura")
     * @ViewElenco( ordine = 4, titolo="Codice sottomisura" )
     */
    protected $cod_sottomisura;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 5, type = "text", label = "Descrizione  della sottomisura")
     */
    protected $desc_sottomisura;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 6, type = "text", label = "Codice della misura")
     * @ViewElenco( ordine = 3, titolo="Codice misura" )
     */
    protected $cod_misura;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 7, type = "text", label = "Descrizione della misura")
     */
    protected $desc_misura;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 8, type = "text", label = "Codice focus area")
     */
    protected $cod_focus_area;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 9, type = "text", label = "Descrizione focus area")
     */
    protected $desc_focus_area;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 10, type = "text", label = "Codice priorità")
     */
    protected $cod_priorita;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 11, type = "text", label = "Descrizione priorità")
     */
    protected $desc_priorita;

    /**
     *
     * @var \MonitoraggioBundle\Entity\TC4Programma
     */
    protected $programma;

    

    

    /**
     * @return mixed
     */
    public function getCodClassificazioneTi()
    {
        return $this->cod_classificazione_ti;
    }

    /**
     * @param mixed $cod_classificazione_ti
     */
    public function setCodClassificazioneTi($cod_classificazione_ti)
    {
        $this->cod_classificazione_ti = $cod_classificazione_ti;
    }

    /**
     * @return mixed
     */
    public function getCodTipoIntervento()
    {
        return $this->cod_tipo_intervento;
    }

    /**
     * @param mixed $cod_tipo_intervento
     */
    public function setCodTipoIntervento($cod_tipo_intervento)
    {
        $this->cod_tipo_intervento = $cod_tipo_intervento;
    }

    /**
     * @return mixed
     */
    public function getDescTipoIntervento()
    {
        return $this->desc_tipo_intervento;
    }

    /**
     * @param mixed $desc_tipo_intervento
     */
    public function setDescTipoIntervento($desc_tipo_intervento)
    {
        $this->desc_tipo_intervento = $desc_tipo_intervento;
    }

    /**
     * @return mixed
     */
    public function getCodSottomisura()
    {
        return $this->cod_sottomisura;
    }

    /**
     * @param mixed $cod_sottomisura
     */
    public function setCodSottomisura($cod_sottomisura)
    {
        $this->cod_sottomisura = $cod_sottomisura;
    }

    /**
     * @return mixed
     */
    public function getDescSottomisura()
    {
        return $this->desc_sottomisura;
    }

    /**
     * @param mixed $desc_sottomisura
     */
    public function setDescSottomisura($desc_sottomisura)
    {
        $this->desc_sottomisura = $desc_sottomisura;
    }

    /**
     * @return mixed
     */
    public function getCodMisura()
    {
        return $this->cod_misura;
    }

    /**
     * @param mixed $cod_misura
     */
    public function setCodMisura($cod_misura)
    {
        $this->cod_misura = $cod_misura;
    }

    /**
     * @return mixed
     */
    public function getDescMisura()
    {
        return $this->desc_misura;
    }

    /**
     * @param mixed $desc_misura
     */
    public function setDescMisura($desc_misura)
    {
        $this->desc_misura = $desc_misura;
    }

    /**
     * @return mixed
     */
    public function getCodFocusArea()
    {
        return $this->cod_focus_area;
    }

    /**
     * @param mixed $cod_focus_area
     */
    public function setCodFocusArea($cod_focus_area)
    {
        $this->cod_focus_area = $cod_focus_area;
    }

    /**
     * @return mixed
     */
    public function getDescFocusArea()
    {
        return $this->desc_focus_area;
    }

    /**
     * @param mixed $desc_focus_area
     */
    public function setDescFocusArea($desc_focus_area)
    {
        $this->desc_focus_area = $desc_focus_area;
    }

    /**
     * @return mixed
     */
    public function getCodPriorita()
    {
        return $this->cod_priorita;
    }

    /**
     * @param mixed $cod_priorita
     */
    public function setCodPriorita($cod_priorita)
    {
        $this->cod_priorita = $cod_priorita;
    }

    /**
     * @return mixed
     */
    public function getDescPriorita()
    {
        return $this->desc_priorita;
    }

    /**
     * @param mixed $desc_priorita
     */
    public function setDescPriorita($desc_priorita)
    {
        $this->desc_priorita = $desc_priorita;
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

   



}
