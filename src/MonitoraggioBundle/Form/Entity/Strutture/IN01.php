<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Entity\Strutture;
use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;
/**
 * Description of AP00
 *
 * @author lfontana
 */
class IN01 extends BaseRicercaStruttura{

    /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice locale progetto" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice locale progetto")
     */
    protected $cod_locale_progetto;
    
     /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Tipo indicatore output" )
     * @RicercaFormType( ordine = 2, type = "choice", label = "Tipo indicatore output", options={"placeholder":"-", "choices":{"COM":"Comune nazionale/comunitario", "DPR":"Definito dal programma"}})
     */
     protected $tipo_indicatore_di_output;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Indicatore", property="indicatore_id" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Indicatore")
     */
    protected $cod_indicatore;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Valore programmato" )
     * @RicercaFormType( ordine = 4, type = "moneta", label = "Valore programmato")
     */
    protected $val_programmato;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Valore realizzato" )
     * @RicercaFormType( ordine = 5, type = "moneta", label = "CValore realizzato")
     */
    protected $valore_realizzato;

    
    /**
     * @return mixed
     */
    public function getCodLocaleProgetto()
    {
        return $this->cod_locale_progetto;
    }

    /**
     * @param mixed $cod_locale_progetto
     */
    public function setCodLocaleProgetto($cod_locale_progetto)
    {
        $this->cod_locale_progetto = $cod_locale_progetto;
    }

    /**
     * @return mixed
     */
    public function getTipoIndicatoreDiOutput()
    {
        return $this->tipo_indicatore_di_output;
    }

    /**
     * @param mixed $tipo_indicatore_di_output
     */
    public function setTipoIndicatoreDiOutput($tipo_indicatore_di_output)
    {
        $this->tipo_indicatore_di_output = $tipo_indicatore_di_output;
    }

    /**
     * @return mixed
     */
    public function getCodIndicatore()
    {
        return $this->cod_indicatore;
    }

    /**
     * @param mixed $cod_indicatore
     */
    public function setCodIndicatore($cod_indicatore)
    {
        $this->cod_indicatore = $cod_indicatore;
    }

    /**
     * @return mixed
     */
    public function getValProgrammato()
    {
        return $this->val_programmato;
    }

    /**
     * @param mixed $val_programmato
     */
    public function setValProgrammato($val_programmato)
    {
        $this->val_programmato = $val_programmato;
    }

    /**
     * @return mixed
     */
    public function getValoreRealizzato()
    {
        return $this->valore_realizzato;
    }

    /**
     * @param mixed $valore_realizzato
     */
    public function setValoreRealizzato($valore_realizzato)
    {
        $this->valore_realizzato = $valore_realizzato;
    }
}
