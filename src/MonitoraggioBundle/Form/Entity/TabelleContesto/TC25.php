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
class TC25 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice ISTAT forma giuridica")
      * @ViewElenco( ordine = 1, titolo="Codice forma giuridica" )
     */
   protected $forma_giuridica;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione ISTAT forma giuridica")
      * @ViewElenco( ordine = 2, titolo="Descrizione ISTAT forma giuridica" )
     */
    protected $descrizione_forma_giuridica;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 3, type = "text", label = "Divisione")
      * @ViewElenco( ordine = 3, titolo="Divisione" )
     */
    protected $divisione;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 4, type = "text", label = "Sezione")
      * @ViewElenco( ordine = 4, titolo="Sezione" )
     */
    protected $sezione;

    
    /**
     * @return mixed
     */
    public function getFormaGiuridica()
    {
        return $this->forma_giuridica;
    }

    /**
     * @param mixed $forma_giuridica
     */
    public function setFormaGiuridica($forma_giuridica)
    {
        $this->forma_giuridica = $forma_giuridica;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneFormaGiuridica()
    {
        return $this->descrizione_forma_giuridica;
    }

    /**
     * @param mixed $descrizione_forma_giuridica
     */
    public function setDescrizioneFormaGiuridica($descrizione_forma_giuridica)
    {
        $this->descrizione_forma_giuridica = $descrizione_forma_giuridica;
    }

    /**
     * @return mixed
     */
    public function getDivisione()
    {
        return $this->divisione;
    }

    /**
     * @param mixed $divisione
     */
    public function setDivisione($divisione)
    {
        $this->divisione = $divisione;
    }

    /**
     * @return mixed
     */
    public function getSezione()
    {
        return $this->sezione;
    }

    /**
     * @param mixed $sezione
     */
    public function setSezione($sezione)
    {
        $this->sezione = $sezione;
    }


}
