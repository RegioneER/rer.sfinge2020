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
class TC12_6 extends Base{
    
    
     /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice dimensione tematica secondaria" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice attività economica")
     */
    protected $cod_classificazione_dts;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Descrizione dimensione tematica secondaria" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Codice attività economica")
     */
    protected $desc_classificazione_dts;

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
    public function getCodClassificazioneDts()
    {
        return $this->cod_classificazione_dts;
    }

    /**
     * @param mixed $cod_classificazione_dts
     */
    public function setCodClassificazioneDts($cod_classificazione_dts)
    {
        $this->cod_classificazione_dts = $cod_classificazione_dts;
    }

    /**
     * @return mixed
     */
    public function getDescClassificazioneDts()
    {
        return $this->desc_classificazione_dts;
    }

    /**
     * @param mixed $desc_classificazione_dts
     */
    public function setDescClassificazioneDts($desc_classificazione_dts)
    {
        $this->desc_classificazione_dts = $desc_classificazione_dts;
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
