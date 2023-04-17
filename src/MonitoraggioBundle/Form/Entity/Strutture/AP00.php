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
class AP00 extends BaseRicercaStruttura{

    
    /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Tipo operazione" )
     * @RicercaFormType( ordine = 4, type = "entity", label = "Tipo operazione", options={"class": "MonitoraggioBundle\Entity\TC5TipoOperazione"})
     */
    protected $tc5_tipo_operazione;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 6, titolo="Tipo di aiuto", show = false  )
     * @RicercaFormType( ordine = 6, type = "entity", label = "Tipo di aiuto", options={"class": "MonitoraggioBundle\Entity\TC6TipoAiuto"})
     */
    protected $tc6_tipo_aiuto;


    /**
     *
     * @var string
     * @ViewElenco( ordine = 10, titolo="Tipo procedura attivazione originaria", show = false  )
     * @RicercaFormType( ordine = 10, type = "entity", label = "Tipo procedura attivazione originaria", options={"class": "MonitoraggioBundle\Entity\TC48TipoProceduraAttivazioneOriginaria"})
     */
    protected $tc48_tipo_procedura_attivazione_originaria;

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
     * @ViewElenco( ordine = 2, titolo="Titolo progetto" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Titolo progetto")
     */
    protected $titolo_progetto;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Sintesi progetto", show = false )
     * @RicercaFormType( ordine = 3, type = "text", label = "Sintesi del progetto")
     */
    protected $sintesi_prg;

   /**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Codice CUP" )
     * @RicercaFormType( ordine = 5, type = "text", label = "Codice CUP")
     */
    protected $cup;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 7, titolo="Data inizio", show = false  )
     * @RicercaFormType( ordine = 7, type = "birthday", label = "Data inizio progetto", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
    protected $data_inizio;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 8, titolo="Data fine prevista", show = false  )
     * @RicercaFormType( ordine = 8, type = "birthday", label = "Data fine prevista", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
    protected $data_fine_prevista;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 9, titolo="Data fine effettiva", show = false  )
     * @RicercaFormType( ordine = 9, type = "birthday", label = "Data fine effettiva", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
    protected $data_fine_effettiva;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 11, titolo="Codice procedura attivazione di origine" )
     * @RicercaFormType( ordine = 11, type = "text", label = "Codice procedura attivazione di origine")
     */
    protected $codice_proc_att_orig;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 12, titolo="Flag cancellazione" )
     * @RicercaFormType( ordine = 12, type = "text", label = "Flag cancellazione")
     */
    protected $flg_cancellazione;

    

    /**
     * @return mixed
     */
    public function getTc5TipoOperazione()
    {
        return $this->tc5_tipo_operazione;
    }

    /**
     * @param mixed $tc5_tipo_operazione
     */
    public function setTc5TipoOperazione($tc5_tipo_operazione)
    {
        $this->tc5_tipo_operazione = $tc5_tipo_operazione;
    }

    /**
     * @return mixed
     */
    public function getTc6TipoAiuto()
    {
        return $this->tc6_tipo_aiuto;
    }

    /**
     * @param mixed $tc6_tipo_aiuto
     */
    public function setTc6TipoAiuto($tc6_tipo_aiuto)
    {
        $this->tc6_tipo_aiuto = $tc6_tipo_aiuto;
    }

    /**
     * @return mixed
     */
    public function getTc48TipoProceduraAttivazioneOriginaria()
    {
        return $this->tc48_tipo_procedura_attivazione_originaria;
    }

    /**
     * @param mixed $tc48_tipo_procedura_attivazione_originaria
     */
    public function setTc48TipoProceduraAttivazioneOriginaria($tc48_tipo_procedura_attivazione_originaria)
    {
        $this->tc48_tipo_procedura_attivazione_originaria = $tc48_tipo_procedura_attivazione_originaria;
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
    public function getTitoloProgetto()
    {
        return $this->titolo_progetto;
    }

    /**
     * @param mixed $titolo_progetto
     */
    public function setTitoloProgetto($titolo_progetto)
    {
        $this->titolo_progetto = $titolo_progetto;
    }

    /**
     * @return mixed
     */
    public function getSintesiPrg()
    {
        return $this->sintesi_prg;
    }

    /**
     * @param mixed $sintesi_prg
     */
    public function setSintesiPrg($sintesi_prg)
    {
        $this->sintesi_prg = $sintesi_prg;
    }

    /**
     * @return mixed
     */
    public function getCup()
    {
        return $this->cup;
    }

    /**
     * @param mixed $cup
     */
    public function setCup($cup)
    {
        $this->cup = $cup;
    }

    /**
     * @return mixed
     */
    public function getDataInizio()
    {
        return $this->data_inizio;
    }

    /**
     * @param mixed $data_inizio
     */
    public function setDataInizio($data_inizio)
    {
        $this->data_inizio = $data_inizio;
    }

    /**
     * @return mixed
     */
    public function getDataFinePrevista()
    {
        return $this->data_fine_prevista;
    }

    /**
     * @param mixed $data_fine_prevista
     */
    public function setDataFinePrevista($data_fine_prevista)
    {
        $this->data_fine_prevista = $data_fine_prevista;
    }

    /**
     * @return mixed
     */
    public function getDataFineEffettiva()
    {
        return $this->data_fine_effettiva;
    }

    /**
     * @param mixed $data_fine_effettiva
     */
    public function setDataFineEffettiva($data_fine_effettiva)
    {
        $this->data_fine_effettiva = $data_fine_effettiva;
    }

    /**
     * @return mixed
     */
    public function getCodiceProcAttOrig()
    {
        return $this->codice_proc_att_orig;
    }

    /**
     * @param mixed $codice_proc_att_orig
     */
    public function setCodiceProcAttOrig($codice_proc_att_orig)
    {
        $this->codice_proc_att_orig = $codice_proc_att_orig;
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
