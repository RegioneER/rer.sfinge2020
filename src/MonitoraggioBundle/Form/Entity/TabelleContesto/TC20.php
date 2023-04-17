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
class TC20 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice attestazione finale")
      * @ViewElenco( ordine = 1, titolo="Codice attestazione finale" )
     */
      protected $cod_attestazione_finale;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione attestazione finale")
      * @ViewElenco( ordine = 2, titolo="Descrizione attestazione finale" )
     */
    protected $descrizione_attestazione_finale;

    /**
     * @return mixed
     */
    public function getCodAttestazioneFinale()
    {
        return $this->cod_attestazione_finale;
    }

    /**
     * @param mixed $cod_attestazione_finale
     */
    public function setCodAttestazioneFinale($cod_attestazione_finale)
    {
        $this->cod_attestazione_finale = $cod_attestazione_finale;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneAttestazioneFinale()
    {
        return $this->descrizione_attestazione_finale;
    }

    /**
     * @param mixed $descrizione_attestazione_finale
     */
    public function setDescrizioneAttestazioneFinale($descrizione_attestazione_finale)
    {
        $this->descrizione_attestazione_finale = $descrizione_attestazione_finale;
    }

}
