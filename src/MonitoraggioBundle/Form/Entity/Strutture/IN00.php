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
class IN00 extends BaseRicercaStruttura{

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
     * @ViewElenco( ordine = 2, titolo="Tipo indicatore risultato" )
     * @RicercaFormType( ordine = 2, type = "choice", label = "Tipo indicatore risultato", options={"placeholder":"-", "choices":{"COM":"Comune nazionale/comunitario", "DPR":"Definito dal programma"}})
     */
     protected $tipo_indicatore_di_risultato;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Indicatore", property="indicatore_id", show=true )
     * @RicercaFormType( ordine = 3, type = "entity", label = "Indicatore", options={"class":"MonitoraggioBundle\Entity\TC42_43IndicatoriRisultato"})
     */
    protected $cod_indicatore;

    
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
    public function getTipoIndicatoreDiRisultato()
    {
        return $this->tipo_indicatore_di_risultato;
    }

    /**
     * @param mixed $tipo_indicatore_di_risultato
     */
    public function setTipoIndicatoreDiRisultato($tipo_indicatore_di_risultato)
    {
        $this->tipo_indicatore_di_risultato = $tipo_indicatore_di_risultato;
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

}
