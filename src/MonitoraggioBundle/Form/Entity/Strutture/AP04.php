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
class AP04 extends BaseRicercaStruttura{
    
    /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Programma" )
     * @RicercaFormType( ordine = 4, type = "entity", label = "Programma", options={"class": "MonitoraggioBundle\Entity\TC4Programma"})
     */
    protected $tc4_programma;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Specifica stato" )
     * @RicercaFormType( ordine = 4, type = "entity", label = "Specifica stato", options={"class": "MonitoraggioBundle\Entity\TC14SpecificaStato"})
     */
    protected $tc14_specifica_stato;

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
     * @ViewElenco( ordine = 4, titolo="Stato" )
     * @RicercaFormType( ordine = 4, type = "choice", label = "Stato", options={"choices": {"1":"Attivo", "2":"Non attivo"}, "placeholder": "-"})
     */
    protected $stato;

   
    /**
     * @return mixed
     */
    public function getTc4Programma()
    {
        return $this->tc4_programma;
    }

    /**
     * @param mixed $tc4_programma
     */
    public function setTc4Programma($tc4_programma)
    {
        $this->tc4_programma = $tc4_programma;
    }

    /**
     * @return mixed
     */
    public function getTc14SpecificaStato()
    {
        return $this->tc14_specifica_stato;
    }

    /**
     * @param mixed $tc14_specifica_stato
     */
    public function setTc14SpecificaStato($tc14_specifica_stato)
    {
        $this->tc14_specifica_stato = $tc14_specifica_stato;
    }

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
    public function getStato()
    {
        return $this->stato;
    }

    /**
     * @param mixed $stato
     */
    public function setStato($stato)
    {
        $this->stato = $stato;
    }


}
