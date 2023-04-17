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
class TC27 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Codice cittadinanza")
      * @ViewElenco( ordine = 1, titolo="Codice cittadinanza" )
     */
   protected $cittadinanza;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione cittadinanza")
      * @ViewElenco( ordine = 2, titolo="Descrizione cittadinanza" )
     */
    protected $descrizione_cittadinanza;

    
    /**
     * @return mixed
     */
    public function getCittadinanza()
    {
        return $this->cittadinanza;
    }

    /**
     * @param mixed $cittadinanza
     */
    public function setCittadinanza($cittadinanza)
    {
        $this->cittadinanza = $cittadinanza;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneCittadinanza()
    {
        return $this->descrizione_cittadinanza;
    }

    /**
     * @param mixed $descrizione_cittadinanza
     */
    public function setDescrizioneCittadinanza($descrizione_cittadinanza)
    {
        $this->descrizione_cittadinanza = $descrizione_cittadinanza;
    }


}
