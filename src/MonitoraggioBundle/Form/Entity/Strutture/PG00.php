<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 12/07/17
 * Time: 15:41
 */


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Entity\Strutture;
use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;


class PG00 extends BaseRicercaStruttura
{

    /**
     * @ViewElenco( ordine = 1, titolo="Motivo assenza CIG" )
     * @RicercaFormType( ordine = 1, type = "entity", label = "Motivo assenza CIG", options={"class": "MonitoraggioBundle\Entity\TC22MotivoAssenzaCIG"})
     */
    protected $tc22_motivo_assenza_cig;


    /**
     * @ViewElenco( ordine = 2, titolo="Tipo procedura aggiudicazione" )
     * @RicercaFormType( ordine = 2, type = "entity", label = "Tipo procedura aggiudicazione", options={"class": "MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione"})
     */
    protected $tc23_tipo_procedura_aggiudicazione;


    /**
     * @ViewElenco( ordine = 3, titolo="Codice locale progetto" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Codice locale progetto")
     */
    protected $cod_locale_progetto;


    /**
     * @ViewElenco( ordine = 4, titolo="Codice procedura aggiudicazione" )
     * @RicercaFormType( ordine = 4, type = "text", label = "Codice procedura aggiudicazione")
     */
    protected $cod_proc_agg;


    /**
     * @ViewElenco( ordine = 5, titolo="CIG" )
     * @RicercaFormType( ordine = 5, type = "text", label = "CIG")
     */
    protected $cig;


    /**
     * @ViewElenco( ordine = 6, titolo="Descrizione procedura aggiudicazione" )
     * @RicercaFormType( ordine = 6, type = "text", label = "Descrizione procedura aggiudicazione")
     */
    protected $descr_procedura_agg;


    /**
     * @ViewElenco( ordine = 7, titolo="Importo procedura aggiudicazione" )
     * @RicercaFormType( ordine = 7, type = "moneta", label = "Importo procedura aggiudicazione")
     */
    protected $importo_procedura_agg;


    /**
     * @ViewElenco( ordine = 8, titolo="Data pubblicazione")
     * @RicercaFormType( ordine = 8, type = "birthday", label = "Data pubblicazione", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
    protected $data_pubblicazione;


    /**
     * @ViewElenco( ordine = 9, titolo="Importo aggiudicato" )
     * @RicercaFormType( ordine = 9, type = "moneta", label = "Importo aggiudicato")
     */
    protected $importo_aggiudicato;


    /**
     * @ViewElenco( ordine = 10, titolo="Data aggiudicazione")
     * @RicercaFormType( ordine = 10, type = "birthday", label = "Data aggiudicazione", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
    protected $data_aggiudicazione;


    /**
     * @ViewElenco( ordine = 11, titolo="Flag cancellazione" )
     * @RicercaFormType( ordine = 11, type = "text", label = "Flag cancellazione")
     */
    protected $flg_cancellazione;



    /**
     * @return mixed
     */
    public function getTc22MotivoAssenzaCig()
    {
        return $this->tc22_motivo_assenza_cig;
    }

    /**
     * @param mixed $tc22_motivo_assenza_cig
     */
    public function setTc22MotivoAssenzaCig($tc22_motivo_assenza_cig)
    {
        $this->tc22_motivo_assenza_cig = $tc22_motivo_assenza_cig;
    }

    /**
     * @return mixed
     */
    public function getTc23TipoProceduraAggiudicazione()
    {
        return $this->tc23_tipo_procedura_aggiudicazione;
    }

    /**
     * @param mixed $tc23_tipo_procedura_aggiudicazione
     */
    public function setTc23TipoProceduraAggiudicazione($tc23_tipo_procedura_aggiudicazione)
    {
        $this->tc23_tipo_procedura_aggiudicazione = $tc23_tipo_procedura_aggiudicazione;
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
    public function getCodProcAgg()
    {
        return $this->cod_proc_agg;
    }

    /**
     * @param mixed $cod_proc_agg
     */
    public function setCodProcAgg($cod_proc_agg)
    {
        $this->cod_proc_agg = $cod_proc_agg;
    }

    /**
     * @return mixed
     */
    public function getCig()
    {
        return $this->cig;
    }

    /**
     * @param mixed $cig
     */
    public function setCig($cig)
    {
        $this->cig = $cig;
    }

    /**
     * @return mixed
     */
    public function getDescrProceduraAgg()
    {
        return $this->descr_procedura_agg;
    }

    /**
     * @param mixed $descr_procedura_agg
     */
    public function setDescrProceduraAgg($descr_procedura_agg)
    {
        $this->descr_procedura_agg = $descr_procedura_agg;
    }

    /**
     * @return mixed
     */
    public function getImportoProceduraAgg()
    {
        return $this->importo_procedura_agg;
    }

    /**
     * @param mixed $importo_procedura_agg
     */
    public function setImportoProceduraAgg($importo_procedura_agg)
    {
        $this->importo_procedura_agg = $importo_procedura_agg;
    }

    /**
     * @return mixed
     */
    public function getDataPubblicazione()
    {
        return $this->data_pubblicazione;
    }

    /**
     * @param mixed $data_pubblicazione
     */
    public function setDataPubblicazione($data_pubblicazione)
    {
        $this->data_pubblicazione = $data_pubblicazione;
    }

    /**
     * @return mixed
     */
    public function getImportoAggiudicato()
    {
        return $this->importo_aggiudicato;
    }

    /**
     * @param mixed $importo_aggiudicato
     */
    public function setImportoAggiudicato($importo_aggiudicato)
    {
        $this->importo_aggiudicato = $importo_aggiudicato;
    }

    /**
     * @return mixed
     */
    public function getDataAggiudicazione()
    {
        return $this->data_aggiudicazione;
    }

    /**
     * @param mixed $data_aggiudicazione
     */
    public function setDataAggiudicazione($data_aggiudicazione)
    {
        $this->data_aggiudicazione = $data_aggiudicazione;
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

