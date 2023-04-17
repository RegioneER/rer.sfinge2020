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
class AP06 extends BaseRicercaStruttura{
    
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
     * @ViewElenco( ordine = 2, titolo="Comune", property="localizzazioneGeografica" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Comune")
     */
    protected $comune;
    
    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Indirizzo" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Indirizzo")
     */
    protected $indirizzo;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Indirizzo" )
     * @RicercaFormType( ordine = 4, type = "text", label = "Programma")
     */
    protected $cod_cap;
    
    public function getCodLocaleProgetto() {
        return $this->cod_locale_progetto;
    }

    public function setCodLocaleProgetto($cod_locale_progetto) {
        $this->cod_locale_progetto = $cod_locale_progetto;
    }

    
    public function getComune() {
        return $this->comune;
    }

    public function setComune($comune) {
        $this->comune = $comune;
    }

        
        /**
     * @return mixed
     */
    public function getIndirizzo()
    {
        return $this->indirizzo;
    }

    /**
     * @param mixed $indirizzo
     */
    public function setIndirizzo($indirizzo)
    {
        $this->indirizzo = $indirizzo;
    }

    /**
     * @return mixed
     */
    public function getCodCap()
    {
        return $this->cod_cap;
    }

    /**
     * @param mixed $cod_cap
     */
    public function setCodCap($cod_cap)
    {
        $this->cod_cap = $cod_cap;
    }



}
