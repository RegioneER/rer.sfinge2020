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
class AP03 extends BaseRicercaStruttura{
    
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
     * @ViewElenco( ordine = 3, titolo="Tipo progetto complesso" )
     * @RicercaFormType( ordine = 3, type = "entity", label = "Tipo progetto complesso", options={"class": "MonitoraggioBundle\Entity\TC11TipoClassificazione"})
     */
    protected $tc11_tipo_classificazione;

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
     * @ViewElenco( ordine = 2, titolo="Classificazione" )
     * @RicercaFormType( ordine = 2, type = "entity", label = "Classificazione",  options={"class": "MonitoraggioBundle\Entity\TC12Classificazione"}))
     */
    protected $classificazione;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 9, titolo="Cancellato", show = false )
     * @RicercaFormType( ordine = 9, type = "choice", label = "Cancellato", options={"choices":{"S":"Sì"}, "placeholder" : "No"})
     */
    protected $flg_cancellazione;

    

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
    public function getTc11TipoClassificazione()
    {
        return $this->tc11_tipo_classificazione;
    }

    /**
     * @param mixed $tc11_tipo_classificazione
     */
    public function setTc11TipoClassificazione($tc11_tipo_classificazione)
    {
        $this->tc11_tipo_classificazione = $tc11_tipo_classificazione;
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
    public function getClassificazione()
    {
        return $this->classificazione;
    }

    /**
     * @return string|null
     */
    public function getFlgCancellazione()
    {
        return $this->flg_cancellazione;
    }

    /**
     * @param string|null $flg_cancellazione
     */
    public function setFlgCancellazione($flg_cancellazione)
    {
        $this->flg_cancellazione = $flg_cancellazione;
    }
}
