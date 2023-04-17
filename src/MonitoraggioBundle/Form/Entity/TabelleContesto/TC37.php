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
 * Description of TC16
 *
 * @author lfontana
 */
class TC37 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice voce spesa")
      * @ViewElenco( ordine = 1, titolo="Codice voce spesa" )
     */
    protected $voce_spesa;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione voce spesa")
      * @ViewElenco( ordine = 2, titolo="Descrizione voce spesa" )
     */
    protected $descrizione_voce_spesa;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 3, type = "text", label = "Codice natura CUP")
      * @ViewElenco( ordine = 3, titolo="Codice natura CUP" )
     */
    protected $codice_natura_cup;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 4, type = "text", label = "Descrizione natura CUP")
      * @ViewElenco( ordine = 4, titolo="Descrizione natura CUP" )
     */
    protected $descrizionenatura_cup;

  

    /**
     * @return mixed
     */
    public function getVoceSpesa()
    {
        return $this->voce_spesa;
    }

    /**
     * @param mixed $voce_spesa
     */
    public function setVoceSpesa($voce_spesa)
    {
        $this->voce_spesa = $voce_spesa;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneVoceSpesa()
    {
        return $this->descrizione_voce_spesa;
    }

    /**
     * @param mixed $descrizione_voce_spesa
     */
    public function setDescrizioneVoceSpesa($descrizione_voce_spesa)
    {
        $this->descrizione_voce_spesa = $descrizione_voce_spesa;
    }

    /**
     * @return mixed
     */
    public function getCodiceNaturaCup()
    {
        return $this->codice_natura_cup;
    }

    /**
     * @param mixed $codice_natura_cup
     */
    public function setCodiceNaturaCup($codice_natura_cup)
    {
        $this->codice_natura_cup = $codice_natura_cup;
    }

    /**
     * @return mixed
     */
    public function getDescrizionenaturaCup()
    {
        return $this->descrizionenatura_cup;
    }

    /**
     * @param mixed $descrizionenatura_cup
     */
    public function setDescrizionenaturaCup($descrizionenatura_cup)
    {
        $this->descrizionenatura_cup = $descrizionenatura_cup;
    }
}
