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
class TC12_5 extends Base{
    
    
     /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice attività economica" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice attività economica")
     */
    protected $cod_classificazione_ae;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Descrizione attività economica" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione attività economica")
     */
    protected $desc_classificazione_ae;

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
    public function getCodClassificazioneAe()
    {
        return $this->cod_classificazione_ae;
    }

    /**
     * @param mixed $cod_classificazione_ae
     */
    public function setCodClassificazioneAe($cod_classificazione_ae)
    {
        $this->cod_classificazione_ae = $cod_classificazione_ae;
    }

    /**
     * @return mixed
     */
    public function getDescClassificazioneAe()
    {
        return $this->desc_classificazione_ae;
    }

    /**
     * @param mixed $desc_classificazione_ae
     */
    public function setDescClassificazioneAe($desc_classificazione_ae)
    {
        $this->desc_classificazione_ae = $desc_classificazione_ae;
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
