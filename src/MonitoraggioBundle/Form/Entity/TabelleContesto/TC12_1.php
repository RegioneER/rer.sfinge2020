<?php

namespace MonitoraggioBundle\Form\Entity\TabelleContesto;

use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TC12_1
 *
 * @author lfontana
 */
class TC12_1 extends Base{
    
    /**
     *
     * @var string
     * @ViewElenco( ordine = 0, titolo="Codice campo di intervento" )
     * @RicercaFormType( ordine = 0, type = "text", label = "Codice campo di intervento")
     */
    protected $cod_classificazione_ci;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Descrizione campo di intervento" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Descrizione campo di intervento")
     */
    protected $desc_classificazione_ci;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Descrizione della specifica del macroaggregato" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Codice tipo classificazione")
     */
    protected $spec_macroaggr_ci;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Codice della specifica del macroaggregato" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Codice della specifica del macroaggregato")
     */
    protected $cod_macroaggr_ci;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Descrizione del codice del macroaggregato" )
     * @RicercaFormType( ordine = 4, type = "text", label = "Descrizione del codice del macroaggregato")
     */
    protected $desc_macroaggr_ci;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Origine" )
     * @RicercaFormType( ordine = 5, type = "text", label = "Origine")
     */
    protected $origine_dato;
    
       /**
     * @return mixed
     */
    public function getCodClassificazioneCi()
    {
        return $this->cod_classificazione_ci;
    }

    /**
     * @param mixed $cod_classificazione_ci
     */
    public function setCodClassificazioneCi($cod_classificazione_ci)
    {
        $this->cod_classificazione_ci = $cod_classificazione_ci;
    }

    /**
     * @return mixed
     */
    public function getDescClassificazioneCi()
    {
        return $this->desc_classificazione_ci;
    }

    /**
     * @param mixed $desc_classificazione_ci
     */
    public function setDescClassificazioneCi($desc_classificazione_ci)
    {
        $this->desc_classificazione_ci = $desc_classificazione_ci;
    }

    /**
     * @return mixed
     */
    public function getSpecMacroaggrCi()
    {
        return $this->spec_macroaggr_ci;
    }

    /**
     * @param mixed $spec_macroaggr_ci
     */
    public function setSpecMacroaggrCi($spec_macroaggr_ci)
    {
        $this->spec_macroaggr_ci = $spec_macroaggr_ci;
    }

    /**
     * @return mixed
     */
    public function getCodMacroaggrCi()
    {
        return $this->cod_macroaggr_ci;
    }

    /**
     * @param mixed $cod_macroaggr_ci
     */
    public function setCodMacroaggrCi($cod_macroaggr_ci)
    {
        $this->cod_macroaggr_ci = $cod_macroaggr_ci;
    }

    /**
     * @return mixed
     */
    public function getDescMacroaggrCi()
    {
        return $this->desc_macroaggr_ci;
    }

    /**
     * @param mixed $desc_macroaggr_ci
     */
    public function setDescMacroaggrCi($desc_macroaggr_ci)
    {
        $this->desc_macroaggr_ci = $desc_macroaggr_ci;
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
