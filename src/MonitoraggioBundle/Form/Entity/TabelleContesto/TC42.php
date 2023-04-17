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
 * @author lfontana
 */
class TC42 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice indicatore risultato")
     * @ViewElenco( ordine = 1, titolo="Codice indicatore" )
     */
   protected $cod_indicatore;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione indicatore risultato")
     * @ViewElenco( ordine = 2, titolo="Descrizione indicatore risultato" )
     */
    protected $descrizione_indicatore;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 3, type = "text", label = "Fonte")
     * @ViewElenco( ordine = 3, titolo="Fonte" )
     */
    protected $fonte_dato;

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
    public function getDescrizioneIndicatore()
    {
        return $this->descrizione_indicatore;
    }

    /**
     * @param mixed $descrizione_indicatore
     */
    public function setDescrizioneIndicatore($descrizione_indicatore)
    {
        $this->descrizione_indicatore = $descrizione_indicatore;
    }

    /**
     * @return mixed
     */
    public function getFonteDato()
    {
        return $this->fonte_dato;
    }

    /**
     * @param mixed $fonte_dato
     */
    public function setFonteDato($fonte_dato)
    {
        $this->fonte_dato = $fonte_dato;
    }
}
