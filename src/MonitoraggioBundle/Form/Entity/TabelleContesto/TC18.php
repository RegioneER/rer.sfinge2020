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
class TC18 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice contenuto formativo")
      * @ViewElenco( ordine = 1, titolo="Codice contenuto formativo" )
     */
    protected $cod_contenuto_formativo;

   /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione contenuto formativo")
      * @ViewElenco( ordine = 2, titolo="Descrizione contenuto formativo" )
     */
    protected $descrizione_contenuto_formativo;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 3, type = "text", label = "Codice settore")
      * @ViewElenco( ordine = 3, titolo="Codice settore" )
     */
    protected $codice_settore;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 4, type = "text", label = "Descrizione settore")
      * @ViewElenco( ordine = 4, titolo="Descrizione settore" )
     */
    protected $descrizione_settore;

    /**
     * @return mixed
     */
    public function getCodContenutoFormativo()
    {
        return $this->cod_contenuto_formativo;
    }

    /**
     * @param mixed $cod_contenuto_formativo
     */
    public function setCodContenutoFormativo($cod_contenuto_formativo)
    {
        $this->cod_contenuto_formativo = $cod_contenuto_formativo;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneContenutoFormativo()
    {
        return $this->descrizione_contenuto_formativo;
    }

    /**
     * @param mixed $descrizione_contenuto_formativo
     */
    public function setDescrizioneContenutoFormativo($descrizione_contenuto_formativo)
    {
        $this->descrizione_contenuto_formativo = $descrizione_contenuto_formativo;
    }

    /**
     * @return mixed
     */
    public function getCodiceSettore()
    {
        return $this->codice_settore;
    }

    /**
     * @param mixed $codice_settore
     */
    public function setCodiceSettore($codice_settore)
    {
        $this->codice_settore = $codice_settore;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneSettore()
    {
        return $this->descrizione_settore;
    }

    /**
     * @param mixed $descrizione_settore
     */
    public function setDescrizioneSettore($descrizione_settore)
    {
        $this->descrizione_settore = $descrizione_settore;
    }
}
