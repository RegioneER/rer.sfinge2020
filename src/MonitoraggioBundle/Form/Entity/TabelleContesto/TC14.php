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
 * Description of TC14
 *
 * @author lfontana
 */
class TC14 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Specifica stato")
      * @ViewElenco( ordine = 1, titolo="Specifica stato" )
     */
     protected $specifica_stato;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "textarea", label = "Descrizione della Specifica stato")
      * @ViewElenco( ordine = 2, titolo="Descrizione della Specifica stato" )
     */
    protected $desc_specifica_stato;

   
    /**
     * @return mixed
     */
    public function getSpecificaStato()
    {
        return $this->specifica_stato;
    }

    /**
     * @param mixed $specifica_stato
     */
    public function setSpecificaStato($specifica_stato)
    {
        $this->specifica_stato = $specifica_stato;
    }

    /**
     * @return mixed
     */
    public function getDescSpecificaStato()
    {
        return $this->desc_specifica_stato;
    }

    /**
     * @param mixed $desc_specifica_stato
     */
    public function setDescSpecificaStato($desc_specifica_stato)
    {
        $this->desc_specifica_stato = $desc_specifica_stato;
    }


}
