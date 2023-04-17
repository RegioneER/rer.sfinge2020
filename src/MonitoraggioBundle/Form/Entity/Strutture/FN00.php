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
class FN00 extends BaseRicercaStruttura{

    
    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Fondo" )
     * @RicercaFormType( ordine = 2, type = "entity", label = "Fondo", options={"class": "MonitoraggioBundle\Entity\TC33FonteFinanziaria"})
     */
     protected $tc33_fonte_finanziaria;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Norma" )
     * @RicercaFormType( ordine = 3, type = "entity", label = "Norma", options={"class": "MonitoraggioBundle\Entity\TC35Norma"})
     */
    protected $tc35_norma;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Delibera CIPE" )
     * @RicercaFormType( ordine = 4, type = "entity", label = "Delibera CIPE", options={"class": "MonitoraggioBundle\Entity\TC34DeliberaCIPE"})
     */
    protected $tc34_delibera_cipe;

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
     * @ViewElenco( ordine = 2, titolo="Localizzazione geografica", property="tc16_localizzazione_geografica" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Comune")
     */
    protected $comune;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Codice fiscale" )
     * @RicercaFormType( ordine = 5, type = "text", label = "Codice fiscale")
     */
    protected $cf_cofinanz;


   /**
     *
     * @var string
     * @ViewElenco( ordine = 6, titolo="Importo" )
     * @RicercaFormType( ordine =6 , type = "moneta", label = "Importo")
     */
    protected $importo;

    

    /**
     * @return mixed
     */
    public function getTc33FonteFinanziaria()
    {
        return $this->tc33_fonte_finanziaria;
    }

    /**
     * @param mixed $tc33_fonte_finanziaria
     */
    public function setTc33FonteFinanziaria($tc33_fonte_finanziaria)
    {
        $this->tc33_fonte_finanziaria = $tc33_fonte_finanziaria;
    }

    /**
     * @return mixed
     */
    public function getTc35Norma()
    {
        return $this->tc35_norma;
    }

    /**
     * @param mixed $tc35_norma
     */
    public function setTc35Norma($tc35_norma)
    {
        $this->tc35_norma = $tc35_norma;
    }

    /**
     * @return mixed
     */
    public function getTc34DeliberaCipe()
    {
        return $this->tc34_delibera_cipe;
    }

    /**
     * @param mixed $tc34_delibera_cipe
     */
    public function setTc34DeliberaCipe($tc34_delibera_cipe)
    {
        $this->tc34_delibera_cipe = $tc34_delibera_cipe;
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
    public function getCfCofinanz()
    {
        return $this->cf_cofinanz;
    }

    /**
     * @param mixed $cf_cofinanz
     */
    public function setCfCofinanz($cf_cofinanz)
    {
        $this->cf_cofinanz = $cf_cofinanz;
    }

    /**
     * @return mixed
     */
    public function getImporto()
    {
        return $this->importo;
    }

    /**
     * @param mixed $importo
     */
    public function setImporto($importo)
    {
        $this->importo = $importo;
    }

    public function getComune() {
        return $this->comune;
    }

    public function setComune($comune) {
        $this->comune = $comune;
    }


}
