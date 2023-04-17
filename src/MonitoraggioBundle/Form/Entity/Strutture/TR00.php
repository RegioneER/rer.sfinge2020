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
class TR00 extends BaseRicercaStruttura{
    
   

    /**
     *
     * @var \MonitoraggioBundle\Entity\TC4Programma
     * @ViewElenco( ordine = 3, titolo="Programma" )
     * @RicercaFormType( ordine = 3, type = "entity", label = "Programma", options={"class": "MonitoraggioBundle\Entity\TC4Programma"})
     */
    protected $tc4_programma;

    /**
     *
     * @var \MonitoraggioBundle\Entity\TC49CausaleTrasferimento
     * @ViewElenco( ordine = 4, titolo="Causale trasferimento" )
     * @RicercaFormType( ordine = 4, type = "entity", label = "Causale trasferimento", options={"class": "MonitoraggioBundle\Entity\TC49CausaleTrasferimento"})
     */
    protected $tc49_causale_trasferimento;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice trasferimento" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice trasferimento")
     */
    protected $cod_trasferimento;

     /**
     *
     * @var \DateTime
     * @ViewElenco( ordine = 2, titolo="Data trasferimento" )
     * @RicercaFormType( ordine = 2, type = "birthday", label = "Data trasferimento", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
    protected $data_trasferimento;

     /**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Importo trasferimento" )
     * @RicercaFormType( ordine = 5, type = "moneta", label = "Importo trasferimento")
     */
    protected $importo_trasferimento;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 6, titolo="Codice fiscale ricevente" )
     * @RicercaFormType( ordine = 6, type = "text", label = "Codice fiscale ricevente")
     */
    protected $cf_sog_ricevente;

     /**
     *
     * @var string
     * @ViewElenco( ordine = 7, titolo="Soggetto pubblico" )
     * @RicercaFormType( ordine = 7, type = "choice", label = "Soggetto pubblico", options={ "choices": {"S": "Sì", "N": "No"}})
     */
    protected $flag_soggetto_pubblico;

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
    public function getTc49CausaleTrasferimento()
    {
        return $this->tc49_causale_trasferimento;
    }

    /**
     * @param mixed $tc49_causale_trasferimento
     */
    public function setTc49CausaleTrasferimento($tc49_causale_trasferimento)
    {
        $this->tc49_causale_trasferimento = $tc49_causale_trasferimento;
    }

    /**
     * @return mixed
     */
    public function getCodTrasferimento()
    {
        return $this->cod_trasferimento;
    }

    /**
     * @param mixed $cod_trasferimento
     */
    public function setCodTrasferimento($cod_trasferimento)
    {
        $this->cod_trasferimento = $cod_trasferimento;
    }

    /**
     * @return mixed
     */
    public function getDataTrasferimento()
    {
        return $this->data_trasferimento;
    }

    /**
     * @param mixed $data_trasferimento
     */
    public function setDataTrasferimento($data_trasferimento)
    {
        $this->data_trasferimento = $data_trasferimento;
    }

    /**
     * @return mixed
     */
    public function getImportoTrasferimento()
    {
        return $this->importo_trasferimento;
    }

    /**
     * @param mixed $importo_trasferimento
     */
    public function setImportoTrasferimento($importo_trasferimento)
    {
        $this->importo_trasferimento = $importo_trasferimento;
    }

    /**
     * @return mixed
     */
    public function getCfSogRicevente()
    {
        return $this->cf_sog_ricevente;
    }

    /**
     * @param mixed $cf_sog_ricevente
     */
    public function setCfSogRicevente($cf_sog_ricevente)
    {
        $this->cf_sog_ricevente = $cf_sog_ricevente;
    }

    /**
     * @return mixed
     */
    public function getFlagSoggettoPubblico()
    {
        return $this->flag_soggetto_pubblico;
    }

    /**
     * @param mixed $flag_soggetto_pubblico
     */
    public function setFlagSoggettoPubblico($flag_soggetto_pubblico)
    {
        $this->flag_soggetto_pubblico = $flag_soggetto_pubblico;
    }


}
