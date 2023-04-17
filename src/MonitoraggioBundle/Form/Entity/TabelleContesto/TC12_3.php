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
class TC12_3 extends Base{
    
    
     /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice tipo territorio" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice tipo territorio")
     */
    protected $cod_classificazione_tt;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Descrizione tipo territorio" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione tipo territorio")
     */
    protected $desc_classificazione_tt;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Origine dato" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Origine del dato")
     */
    protected $origine_dato;

    
    /**
     * @return mixed
     */
    public function getCodClassificazioneTt()
    {
        return $this->cod_classificazione_tt;
    }

    /**
     * @param mixed $cod_classificazione_tt
     */
    public function setCodClassificazioneTt($cod_classificazione_tt)
    {
        $this->cod_classificazione_tt = $cod_classificazione_tt;
    }

    /**
     * @return mixed
     */
    public function getDescClassificazioneTt()
    {
        return $this->desc_classificazione_tt;
    }

    /**
     * @param mixed $desc_classificazione_tt
     */
    public function setDescClassificazioneTt($desc_classificazione_tt)
    {
        $this->desc_classificazione_tt = $desc_classificazione_tt;
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
