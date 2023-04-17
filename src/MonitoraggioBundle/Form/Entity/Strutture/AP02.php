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
class AP02 extends BaseRicercaStruttura{
    
    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Tipo progetto complesso" )
     * @RicercaFormType( ordine = 2, type = "entity", label = "Tipo progetto complesso", options={"class": "MonitoraggioBundle\Entity\TC7ProgettoComplesso"})
     */
    protected $tc7_progetto_complesso;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Grande progetto" )
     * @RicercaFormType( ordine = 4, type = "entity", label = "Grande progetto", options={"class": "MonitoraggioBundle\Entity\TC8GrandeProgetto"})
     */
    protected $tc8_grande_progetto;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Tipo istituzione finanziaria" )
     * @RicercaFormType( ordine = 5, type = "entity", label = "Tipo istituzione finanziaria", options={"class": "MonitoraggioBundle\Entity\TC9TipoLivelloIstituzione"})
     */
    protected $tc9_tipo_livello_istituzione;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 7, titolo="Tipo localizzazione", show = false )
     * @RicercaFormType( ordine = 7, type = "entity", label = "Tipo localizzazione", options={"class": "MonitoraggioBundle\Entity\TC10TipoLocalizzazione"})
     */
    protected $tc10_tipo_localizzazione;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 8, titolo="Tipo operazione", show = false )
     * @RicercaFormType( ordine = 8, type = "entity", label = "Tipo operazione", options={"class": "MonitoraggioBundle\Entity\TC13GruppoVulnerabileProgetto"})
     */
    protected $tc13_gruppo_vulnerabile_progetto;

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
     * @ViewElenco( ordine = 4, titolo="Generatore di entrate" , show =false)
     * @RicercaFormType( ordine = 4, type = "choice", label = "Generatore di entrate", options={"choices":{"S":"Sì", "N":"No"}, "placeholder": "-"})
     */
    protected $generatore_entrate;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 6, titolo="Fondo di fondi" , show =false)
     * @RicercaFormType( ordine = 6, type = "choice", label = "Generatore di entrate", options={"choices":{"S":"Sì", "N":"No"}, "placeholder": "-"})
     */
    protected $fondo_di_fondi;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 9, titolo="Cancellato", show = false )
     * @RicercaFormType( ordine = 9, type = "choice", label = "Cancellato", options={"choices":{"S":"Sì"}, "placeholder" : "No"})
     */
    protected $flg_cancellazione;

   

    /**
     * @return mixed
     */
    public function getTc7ProgettoComplesso()
    {
        return $this->tc7_progetto_complesso;
    }

    /**
     * @param mixed $tc7_progetto_complesso
     */
    public function setTc7ProgettoComplesso($tc7_progetto_complesso)
    {
        $this->tc7_progetto_complesso = $tc7_progetto_complesso;
    }

    /**
     * @return mixed
     */
    public function getTc8GrandeProgetto()
    {
        return $this->tc8_grande_progetto;
    }

    /**
     * @param mixed $tc8_grande_progetto
     */
    public function setTc8GrandeProgetto($tc8_grande_progetto)
    {
        $this->tc8_grande_progetto = $tc8_grande_progetto;
    }

    /**
     * @return mixed
     */
    public function getTc9TipoLivelloIstituzione()
    {
        return $this->tc9_tipo_livello_istituzione;
    }

    /**
     * @param mixed $tc9_tipo_livello_istituzione
     */
    public function setTc9TipoLivelloIstituzione($tc9_tipo_livello_istituzione)
    {
        $this->tc9_tipo_livello_istituzione = $tc9_tipo_livello_istituzione;
    }

    /**
     * @return mixed
     */
    public function getTc10TipoLocalizzazione()
    {
        return $this->tc10_tipo_localizzazione;
    }

    /**
     * @param mixed $tc10_tipo_localizzazione
     */
    public function setTc10TipoLocalizzazione($tc10_tipo_localizzazione)
    {
        $this->tc10_tipo_localizzazione = $tc10_tipo_localizzazione;
    }

    /**
     * @return mixed
     */
    public function getTc13GruppoVulnerabileProgetto()
    {
        return $this->tc13_gruppo_vulnerabile_progetto;
    }

    /**
     * @param mixed $tc13_gruppo_vulnerabile_progetto
     */
    public function setTc13GruppoVulnerabileProgetto($tc13_gruppo_vulnerabile_progetto)
    {
        $this->tc13_gruppo_vulnerabile_progetto = $tc13_gruppo_vulnerabile_progetto;
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
    public function getGeneratoreEntrate()
    {
        return $this->generatore_entrate;
    }

    /**
     * @param mixed $generatore_entrate
     */
    public function setGeneratoreEntrate($generatore_entrate)
    {
        $this->generatore_entrate = $generatore_entrate;
    }

    /**
     * @return mixed
     */
    public function getFondoDiFondi()
    {
        return $this->fondo_di_fondi;
    }

    /**
     * @param mixed $fondo_di_fondi
     */
    public function setFondoDiFondi($fondo_di_fondi)
    {
        $this->fondo_di_fondi = $fondo_di_fondi;
    }

    /**
     * @return mixed
     */
    public function getFlgCancellazione()
    {
        return $this->flg_cancellazione;
    }

    /**
     * @param mixed $flg_cancellazione
     */
    public function setFlgCancellazione($flg_cancellazione)
    {
        $this->flg_cancellazione = $flg_cancellazione;
    }
}
