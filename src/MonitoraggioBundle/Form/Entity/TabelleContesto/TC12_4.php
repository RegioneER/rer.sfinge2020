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
class TC12_4 extends Base{
    
    
     /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice meccanismo erogazione territoriale" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice meccanismo erogazione territoriale")
     */
    protected $cod_classificazione_met;

     /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Descrizione meccanismo erogazione territoriale" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione meccanismo erogazione territoriale")
     */
    protected $desc_classificazione_met;

     /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Origine dato" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Origine dato")
     */
    protected $origine_dato;

   
    /**
     * @return mixed
     */
    public function getCodClassificazioneMet()
    {
        return $this->cod_classificazione_met;
    }

    /**
     * @param mixed $cod_classificazione_met
     */
    public function setCodClassificazioneMet($cod_classificazione_met)
    {
        $this->cod_classificazione_met = $cod_classificazione_met;
    }

    /**
     * @return mixed
     */
    public function getDescClassificazioneMet()
    {
        return $this->desc_classificazione_met;
    }

    /**
     * @param mixed $desc_classificazione_met
     */
    public function setDescClassificazioneMet($desc_classificazione_met)
    {
        $this->desc_classificazione_met = $desc_classificazione_met;
    }

    /**
     * @return mixed
     */
    public function getOrigineDato()
    {
        return $this->origine_dato;
    }

    /**
     * @param mixed $origine_dato
     */
    public function setOrigineDato($origine_dato)
    {
        $this->origine_dato = $origine_dato;
    }

}
