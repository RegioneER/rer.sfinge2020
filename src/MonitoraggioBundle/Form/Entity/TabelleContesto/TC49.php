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
class TC49 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Codice causale trasferimento")
      * @ViewElenco( ordine = 1, titolo="Codice causale trasferimento" )
     */
   protected $causale_trasferimento;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione causale trasferimento")
      * @ViewElenco( ordine = 2, titolo="Descrizione causale trasferimento" )
     */
    protected $descrizione_causale_trasferimento;

    /**
     * @return mixed
     */
    public function getCausaleTrasferimento()
    {
        return $this->causale_trasferimento;
    }

    /**
     * @param mixed $causale_trasferimento
     */
    public function setCausaleTrasferimento($causale_trasferimento)
    {
        $this->causale_trasferimento = $causale_trasferimento;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneCausaleTrasferimento()
    {
        return $this->descrizione_causale_trasferimento;
    }

    /**
     * @param mixed $descrizione_causale_trasferimento
     */
    public function setDescrizioneCausaleTrasferimento($descrizione_causale_trasferimento)
    {
        $this->descrizione_causale_trasferimento = $descrizione_causale_trasferimento;
    }







}
