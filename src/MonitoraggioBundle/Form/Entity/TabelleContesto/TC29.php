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
class TC29 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Codice condizione di mercato")
      * @ViewElenco( ordine = 1, titolo="Codice condizione di mercato" )
     */
   protected $cond_mercato_ingresso;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione condizione di mercato")
      * @ViewElenco( ordine = 2, titolo="Descrizione condizione di mercato" )
     */
    protected $descrizione_condizione_mercato;

    /**
     * @return mixed
     */
    public function getCondMercatoIngresso()
    {
        return $this->cond_mercato_ingresso;
    }

    /**
     * @param mixed $cond_mercato_ingresso
     */
    public function setCondMercatoIngresso($cond_mercato_ingresso)
    {
        $this->cond_mercato_ingresso = $cond_mercato_ingresso;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneCondizioneMercato()
    {
        return $this->descrizione_condizione_mercato;
    }

    /**
     * @param mixed $descrizione_condizione_mercato
     */
    public function setDescrizioneCondizioneMercato($descrizione_condizione_mercato)
    {
        $this->descrizione_condizione_mercato = $descrizione_condizione_mercato;
    }



}
