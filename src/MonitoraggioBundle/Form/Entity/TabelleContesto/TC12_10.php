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
 * Description of TC12_10
 *
 * @author lfontana
 */
class TC12_10 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice classificazione linea azione")
      * @ViewElenco( ordine = 1, titolo="Codice classificazione linea azione" )
     */
    protected $cod_classificazione_la;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Codice linea azione")
      * @ViewElenco( ordine = 2, titolo="Codice linea azione" )
     */
    protected $cod_linea_azione;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 3, type = "text", label = "Descrizione linea azione")
      * @ViewElenco( ordine = 3, titolo="Descrizione linea azione" )
     */
    protected $desc_linea_azione;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 4, type = "text", label = "Codice risultato atteso")
      * @ViewElenco( ordine = 4, titolo="Codice risultato atteso" )
     */
    protected $cod_classificazione_ra;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 5, type = "text", label = "Descrizione risultato atteso")
      * @ViewElenco( ordine = 5, titolo="Descrizione risultato atteso" )
     */
    protected $desc_classificazione_ra;

    /**
     *
     * @var string
      * @ViewElenco( ordine = 6, titolo="Programma" )
     */
    protected $programma;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 7, type = "text", label = "Origine dato")
     */
    protected $origine_dato;

  
    /**
     * @return mixed
     */
    public function getCodClassificazioneLa()
    {
        return $this->cod_classificazione_la;
    }

    /**
     * @param mixed $cod_classificazione_la
     */
    public function setCodClassificazioneLa($cod_classificazione_la)
    {
        $this->cod_classificazione_la = $cod_classificazione_la;
    }

    /**
     * @return mixed
     */
    public function getCodLineaAzione()
    {
        return $this->cod_linea_azione;
    }

    /**
     * @param mixed $cod_linea_azione
     */
    public function setCodLineaAzione($cod_linea_azione)
    {
        $this->cod_linea_azione = $cod_linea_azione;
    }

    /**
     * @return mixed
     */
    public function getDescLineaAzione()
    {
        return $this->desc_linea_azione;
    }

    /**
     * @param mixed $desc_linea_azione
     */
    public function setDescLineaAzione($desc_linea_azione)
    {
        $this->desc_linea_azione = $desc_linea_azione;
    }

    /**
     * @return mixed
     */
    public function getCodClassificazioneRa()
    {
        return $this->cod_classificazione_ra;
    }

    /**
     * @param mixed $cod_classificazione_ra
     */
    public function setCodClassificazioneRa($cod_classificazione_ra)
    {
        $this->cod_classificazione_ra = $cod_classificazione_ra;
    }

    /**
     * @return mixed
     */
    public function getDescClassificazioneRa()
    {
        return $this->desc_classificazione_ra;
    }

    /**
     * @param mixed $desc_classificazione_ra
     */
    public function setDescClassificazioneRa($desc_classificazione_ra)
    {
        $this->desc_classificazione_ra = $desc_classificazione_ra;
    }

    /**
     * @return mixed
     */
    public function getProgramma()
    {
        return $this->programma;
    }

    
    public function setProgramma($programma)
    {
        $this->programma = $programma;
    }

    /**
     * @return mixed
     */
    public function getOrigineDato()
    {
        return $this->origine_dato;
    }

    /**
     * @param mixed $origine_dato
     */
    public function setOrigineDato($origine_dato)
    {
        $this->origine_dato = $origine_dato;
    }

}
