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
class TC12_7 extends Base{
    
    
     /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice risultato atteso" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice risultato atteso")
     */
   protected $cod_classificazione_ra;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Descrizione risultato atteso" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione risultato atteso")
     */
    protected $desc_classificazione_ra;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Codice obiettivo tematico" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Codice obiettivo tematico")
     */
    protected $cod_obiettivo_tem;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Descrizione obiettivo tematico" )
     * @RicercaFormType( ordine = 4, type = "text", label = "Descrizione obiettivo tematico")
     */
    protected $desc_obiettivo_tem;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Origine dato" )
     * @RicercaFormType( ordine = 5, type = "text", label = "Origine del dato")
     */
    protected $origine_dato;

  
    /**
     * @return mixed
     */
    public function getCodClassificazioneRa()
    {
        return $this->cod_classificazione_ra;
    }

    /**
     * @param mixed $cod_classificazione_ra
     */
    public function setCodClassificazioneRa($cod_classificazione_ra)
    {
        $this->cod_classificazione_ra = $cod_classificazione_ra;
    }

    /**
     * @return mixed
     */
    public function getDescClassificazioneRa()
    {
        return $this->desc_classificazione_ra;
    }

    /**
     * @param mixed $desc_classificazione_ra
     */
    public function setDescClassificazioneRa($desc_classificazione_ra)
    {
        $this->desc_classificazione_ra = $desc_classificazione_ra;
    }

    /**
     * @return mixed
     */
    public function getCodObiettivoTem()
    {
        return $this->cod_obiettivo_tem;
    }

    /**
     * @param mixed $cod_obiettivo_tem
     */
    public function setCodObiettivoTem($cod_obiettivo_tem)
    {
        $this->cod_obiettivo_tem = $cod_obiettivo_tem;
    }

    /**
     * @return mixed
     */
    public function getDescObiettivoTem()
    {
        return $this->desc_obiettivo_tem;
    }

    /**
     * @param mixed $desc_obiettivo_tem
     */
    public function setDescObiettivoTem($desc_obiettivo_tem)
    {
        $this->desc_obiettivo_tem = $desc_obiettivo_tem;
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
