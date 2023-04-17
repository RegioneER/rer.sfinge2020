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
class FN04 extends BaseRicercaStruttura{

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
     * @ViewElenco( ordine = 6, titolo="Causale disimpegno" )
     * @RicercaFormType( ordine = 6, type = "entity", label = "Causale disimpegno", options={"class": "MonitoraggioBundle\Entity\TC38CausaleDisimpegno"})
     */
      protected $tc38_causale_disimpegno;

  
    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Codice impegno" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Codice impegno")
     */
    protected $cod_impegno;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Tipologia impegno" )
     * @RicercaFormType( ordine = 3, type = "choice", label = "Tipologia impegno", options={"placeholder":"-", "choices":{"I":"Impegno", "DI":"Disimpegno", "I-TR":"Impegno per trasferimento","D-TR":"Diseimpegno per trasferimento"}})
     */
    protected $tipologia_impegno;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Data impegno" )
     * @RicercaFormType( ordine = 4, type = "birthday", label = "Data impegno", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
    protected $data_impegno;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Importo impegno" )
     * @RicercaFormType( ordine = 5, type = "moneta", label = "Importo impegno")
     */
    protected $importo_impegno;

   /**
     *
     * @var string
     * @ViewElenco( ordine = 7, titolo="Note impegno", show=false )
     * @RicercaFormType( ordine = 7, type = "text", label = "Note impegno")
     */
    protected $note_impegno;

   

    /**
     * @return mixed
     */
    public function getTc38CausaleDisimpegno()
    {
        return $this->tc38_causale_disimpegno;
    }

    /**
     * @param mixed $tc38_causale_disimpegno
     */
    public function setTc38CausaleDisimpegno($tc38_causale_disimpegno)
    {
        $this->tc38_causale_disimpegno = $tc38_causale_disimpegno;
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
    public function getCodImpegno()
    {
        return $this->cod_impegno;
    }

    /**
     * @param mixed $cod_impegno
     */
    public function setCodImpegno($cod_impegno)
    {
        $this->cod_impegno = $cod_impegno;
    }

    /**
     * @return mixed
     */
    public function getTipologiaImpegno()
    {
        return $this->tipologia_impegno;
    }

    /**
     * @param mixed $tipologia_impegno
     */
    public function setTipologiaImpegno($tipologia_impegno)
    {
        $this->tipologia_impegno = $tipologia_impegno;
    }

    /**
     * @return mixed
     */
    public function getDataImpegno()
    {
        return $this->data_impegno;
    }

    /**
     * @param mixed $data_impegno
     */
    public function setDataImpegno($data_impegno)
    {
        $this->data_impegno = $data_impegno;
    }

    /**
     * @return mixed
     */
    public function getImportoImpegno()
    {
        return $this->importo_impegno;
    }

    /**
     * @param mixed $importo_impegno
     */
    public function setImportoImpegno($importo_impegno)
    {
        $this->importo_impegno = $importo_impegno;
    }

    /**
     * @return mixed
     */
    public function getNoteImpegno()
    {
        return $this->note_impegno;
    }

    /**
     * @param mixed $note_impegno
     */
    public function setNoteImpegno($note_impegno)
    {
        $this->note_impegno = $note_impegno;
    }


}
