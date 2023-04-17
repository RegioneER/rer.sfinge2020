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
class TC35 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Codice norma")
      * @ViewElenco( ordine = 1, titolo="Codice norma" )
     */
   protected $cod_norma;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 2, type = "text", label = "Tipologia norma")
      * @ViewElenco( ordine = 2, titolo="Tipologia norma" )
     */
    protected $tipo_norma;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 3, type = "text", label = "Descrizione norma")
      * @ViewElenco( ordine = 3, titolo="Descrizione norma" )
     */
    protected $descrizione_norma;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 4, type = "text", label = "Numero norma")
      * @ViewElenco( ordine = 4, titolo="Numero norma" )
     */
    protected $numero_norma;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 5, type = "text", label = "Anno norma")
      * @ViewElenco( ordine = 5, titolo="Anno norma" )
     */
    protected $anno_norma;

    
    /**
     * @return mixed
     */
    public function getCodNorma()
    {
        return $this->cod_norma;
    }

    /**
     * @param mixed $cod_norma
     */
    public function setCodNorma($cod_norma)
    {
        $this->cod_norma = $cod_norma;
    }

    /**
     * @return mixed
     */
    public function getTipoNorma()
    {
        return $this->tipo_norma;
    }

    /**
     * @param mixed $tipo_norma
     */
    public function setTipoNorma($tipo_norma)
    {
        $this->tipo_norma = $tipo_norma;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneNorma()
    {
        return $this->descrizione_norma;
    }

    /**
     * @param mixed $descrizione_norma
     */
    public function setDescrizioneNorma($descrizione_norma)
    {
        $this->descrizione_norma = $descrizione_norma;
    }

    /**
     * @return mixed
     */
    public function getNumeroNorma()
    {
        return $this->numero_norma;
    }

    /**
     * @param mixed $numero_norma
     */
    public function setNumeroNorma($numero_norma)
    {
        $this->numero_norma = $numero_norma;
    }

    /**
     * @return mixed
     */
    public function getAnnoNorma()
    {
        return $this->anno_norma;
    }

    /**
     * @param mixed $anno_norma
     */
    public function setAnnoNorma($anno_norma)
    {
        $this->anno_norma = $anno_norma;
    }


}
