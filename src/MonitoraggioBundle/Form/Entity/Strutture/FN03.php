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
class FN03 extends BaseRicercaStruttura{

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
     * @ViewElenco( ordine = 2, titolo="Anno" )
     * @RicercaFormType( ordine = 2, type = "integer", label = "Anno di riferimento")
     */
     protected $anno_piano;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Importo realizzato" )
     * @RicercaFormType( ordine = 3, type = "moneta", label = "Codice locale progetto")
     */
    protected $imp_realizzato;

     /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Importo da realizzare" )
     * @RicercaFormType( ordine = 4, type = "moneta", label = "Importo da realizzare")
     */
    protected $imp_da_realizzare;


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
    public function getAnnoPiano()
    {
        return $this->anno_piano;
    }

    /**
     * @param mixed $anno_piano
     */
    public function setAnnoPiano($anno_piano)
    {
        $this->anno_piano = $anno_piano;
    }

    /**
     * @return mixed
     */
    public function getImpRealizzato()
    {
        return $this->imp_realizzato;
    }

    /**
     * @param mixed $imp_realizzato
     */
    public function setImpRealizzato($imp_realizzato)
    {
        $this->imp_realizzato = $imp_realizzato;
    }

    /**
     * @return mixed
     */
    public function getImpDaRealizzare()
    {
        return $this->imp_da_realizzare;
    }

    /**
     * @param mixed $imp_da_realizzare
     */
    public function setImpDaRealizzare($imp_da_realizzare)
    {
        $this->imp_da_realizzare = $imp_da_realizzare;
    }


}
