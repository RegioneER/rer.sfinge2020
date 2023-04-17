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
class AP05 extends BaseRicercaStruttura{
    
    /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Strumento attuativo" )
     * @RicercaFormType( ordine = 4, type = "entity", label = "Strumento attuativo", options={"class": "MonitoraggioBundle\Entity\TC15StrumentoAttuativo"})
     */
     protected $tc15_strumento_attuativo;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice locale progetto" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice locale progetto")
     */
    protected $cod_locale_progetto;

  

    /**
     * @return mixed
     */
    public function getTc15StrumentoAttuativo()
    {
        return $this->tc15_strumento_attuativo;
    }

    /**
     * @param mixed $tc15_strumento_attuativo
     */
    public function setTc15StrumentoAttuativo($tc15_strumento_attuativo)
    {
        $this->tc15_strumento_attuativo = $tc15_strumento_attuativo;
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

  

}
