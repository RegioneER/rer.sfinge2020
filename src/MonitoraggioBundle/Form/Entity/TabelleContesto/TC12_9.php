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
 * Description of TC9
 *
 * @author lfontana
 */
class TC12_9 extends Base{
    
    /**
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice classificazione" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice classificazione")
     */
     protected $cod_classificazione;

    /**
     * @ViewElenco( ordine = 2, titolo="Descrizione classificazione" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione classificazione")
     */
    protected $desc_classificazione;

    /**
     *@ViewElenco( ordine = 3, titolo="Tipo classificazione" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Tipo classificazione")
     */
    protected $tipo_class;

    /**
     * @ViewElenco( ordine = 4, titolo="Codice raggruppamento" )
     * @RicercaFormType( ordine = 4, type = "text", label = "Codice raggruppamento")
     */
    protected $cod_raggruppamento;

    /**
     * @ViewElenco( ordine = 5, titolo="Descrizione raggruppamento" )
     * @RicercaFormType( ordine = 5, type = "text", label = "Descrizione raggruppamento")
     */
    protected $desc_raggruppamento;

    /**
     * @RicercaFormType( ordine = 6, type = "text", label = "Origine del dato")
     */
    protected $origine_dato;

    
    /**
     * @return mixed
     */
    public function getCodClassificazione()
    {
        return $this->cod_classificazione;
    }

    /**
     * @param mixed $cod_classificazione
     */
    public function setCodClassificazione($cod_classificazione)
    {
        $this->cod_classificazione = $cod_classificazione;
    }

    /**
     * @return mixed
     */
    public function getDescClassificazione()
    {
        return $this->desc_classificazione;
    }

    /**
     * @param mixed $desc_classificazione
     */
    public function setDescClassificazione($desc_classificazione)
    {
        $this->desc_classificazione = $desc_classificazione;
    }

    /**
     * @return mixed
     */
    public function getTipoClass()
    {
        return $this->tipo_class;
    }

    /**
     * @param mixed $tipo_class
     */
    public function setTipoClass($tipo_class)
    {
        $this->tipo_class = $tipo_class;
    }

    /**
     * @return mixed
     */
    public function getCodRaggruppamento()
    {
        return $this->cod_raggruppamento;
    }

    /**
     * @param mixed $cod_raggruppamento
     */
    public function setCodRaggruppamento($cod_raggruppamento)
    {
        $this->cod_raggruppamento = $cod_raggruppamento;
    }

    /**
     * @return mixed
     */
    public function getDescRaggruppamento()
    {
        return $this->desc_raggruppamento;
    }

    /**
     * @param mixed $desc_raggruppamento
     */
    public function setDescRaggruppamento($desc_raggruppamento)
    {
        $this->desc_raggruppamento = $desc_raggruppamento;
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
