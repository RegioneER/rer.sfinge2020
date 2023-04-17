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
/**
 * Description of TC16
 *
 * @author lfontana
 */
class TC32 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice partecipazione corso")
      * @ViewElenco( ordine = 1, titolo="Codice partecipazione corso" )
     */
    protected $stato_partecipante;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione effettiva partecipazione corso")
      * @ViewElenco( ordine = 2, titolo="Descrizione effettiva partecipazione corso" )
     */
    protected $descrizione_stato_partecipante;

  
    /**
     * @return mixed
     */
    public function getStatoPartecipante()
    {
        return $this->stato_partecipante;
    }

    /**
     * @param mixed $stato_partecipante
     */
    public function setStatoPartecipante($stato_partecipante)
    {
        $this->stato_partecipante = $stato_partecipante;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneStatoPartecipante()
    {
        return $this->descrizione_stato_partecipante;
    }

    /**
     * @param mixed $descrizione_stato_partecipante
     */
    public function setDescrizioneStatoPartecipante($descrizione_stato_partecipante)
    {
        $this->descrizione_stato_partecipante = $descrizione_stato_partecipante;
    }



}
