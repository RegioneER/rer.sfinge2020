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
class SC00 extends BaseRicercaStruttura{
    
   

    /**
     *
     * @var \MonitoraggioBundle\Entity\TC24RuoloSoggetto
     * @ViewElenco( ordine = 2, titolo="Ruolo del soggetto" , show=false )
     * @RicercaFormType( ordine = 2, type = "entity", label = "Ruolo del soggetto", options={"class": "MonitoraggioBundle\Entity\TC24RuoloSoggetto"})
     */
   protected $tc24_ruolo_soggetto;

    /**
     *
     * @var \MonitoraggioBundle\Entity\TC25FormaGiuridica
     * @ViewElenco( ordine = 7, titolo="Forma giuridica" , show=false )
     * @RicercaFormType( ordine = 7, type = "entity", label = "Forma giuridica", options={"class": "MonitoraggioBundle\Entity\TC25FormaGiuridica"})
     */
    protected $tc25_forma_giuridica;

    /**
     *
     * @var \MonitoraggioBundle\Entity\TC26Ateco
     * @ViewElenco( ordine = 8, titolo="Ateco" , show=false )
     * @RicercaFormType( ordine = 8, type = "entity", label = "Classificazione Ateco", options={"class": "MonitoraggioBundle\Entity\TC26Ateco"})
     */
    protected $tc26_ateco;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice progetto locale" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice progetto locale")
     */
    protected $cod_locale_progetto;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Codice fiscale" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Codice fiscale")
     */
    protected $codice_fiscale;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Soggetto pubblico" )
     * @RicercaFormType( ordine = 4, type = "choice", label = "Soggetto pubblico", options={"choices":{"S":"sì","N":"No"}})
     */
    protected $flag_soggetto_pubblico;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Codice indice pubblica amministrazione" )
     * @RicercaFormType( ordine = 5, type = "text", label = "Codice indice pubblica amministrazione*")
     */
    protected $cod_uni_ipa;

   /**
     *
     * @var string
     * @ViewElenco( ordine = 6, titolo="Denominazione soggetto" )
     * @RicercaFormType( ordine = 6, type = "text", label = "Denominazione soggetto")
     */
    protected $denominazione_sog;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 9, titolo="Note" , show = false)
     * @RicercaFormType( ordine = 9, type = "text", label = "Note")
     */
    protected $note;

    
    /**
     * @return mixed
     */
    public function getTc24RuoloSoggetto()
    {
        return $this->tc24_ruolo_soggetto;
    }

    /**
     * @param mixed $tc24_ruolo_soggetto
     */
    public function setTc24RuoloSoggetto($tc24_ruolo_soggetto)
    {
        $this->tc24_ruolo_soggetto = $tc24_ruolo_soggetto;
    }

    /**
     * @return mixed
     */
    public function getTc25FormaGiuridica()
    {
        return $this->tc25_forma_giuridica;
    }

    /**
     * @param mixed $tc25_forma_giuridica
     */
    public function setTc25FormaGiuridica($tc25_forma_giuridica)
    {
        $this->tc25_forma_giuridica = $tc25_forma_giuridica;
    }

    /**
     * @return mixed
     */
    public function getTc26Ateco()
    {
        return $this->tc26_ateco;
    }

    /**
     * @param mixed $tc26_ateco
     */
    public function setTc26Ateco($tc26_ateco)
    {
        $this->tc26_ateco = $tc26_ateco;
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
    public function getCodiceFiscale()
    {
        return $this->codice_fiscale;
    }

    /**
     * @param mixed $codice_fiscale
     */
    public function setCodiceFiscale($codice_fiscale)
    {
        $this->codice_fiscale = $codice_fiscale;
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

    /**
     * @return mixed
     */
    public function getCodUniIpa()
    {
        return $this->cod_uni_ipa;
    }

    /**
     * @param mixed $cod_uni_ipa
     */
    public function setCodUniIpa($cod_uni_ipa)
    {
        $this->cod_uni_ipa = $cod_uni_ipa;
    }

    /**
     * @return mixed
     */
    public function getDenominazioneSog()
    {
        return $this->denominazione_sog;
    }

    /**
     * @param mixed $denominazione_sog
     */
    public function setDenominazioneSog($denominazione_sog)
    {
        $this->denominazione_sog = $denominazione_sog;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }
   
}
