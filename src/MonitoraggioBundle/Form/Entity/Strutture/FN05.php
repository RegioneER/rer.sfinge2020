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
class FN05 extends BaseRicercaStruttura{

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
     * @ViewElenco( ordine = 5, titolo="Programma", show = false )
     * @RicercaFormType( ordine = 5, type = "entity", label = "Programma", options={"class": "MonitoraggioBundle\Entity\TC4Programma"})
     */
      protected $tc4_programma;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 6, titolo="Livello gerarchico" , show = false)
     * @RicercaFormType( ordine = 6, type = "text", label = "Livello gerarchico")
     */
    protected $tc36_livello_gerarchico;

     /**
     *
     * @var string
     * @ViewElenco( ordine = 9, titolo="Causale disimpegno", property="tc38_causale_disimpegno_amm" )
     * @RicercaFormType( ordine = 9, type = "entity", label = "Causale disimpegno", options={"class": "MonitoraggioBundle\Entity\TC38CausaleDisimpegno"})
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
     * @ViewElenco( ordine = 4, titolo="Data impegno" , show = false)
     * @RicercaFormType( ordine = 4, type = "birthday", label = "Data impegno", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
    protected $data_impegno;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 7, titolo="Data impegno ammesso" )
     * @RicercaFormType( ordine = 7, type = "birthday", label = "Data impegno ammesso", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
    protected $data_imp_amm;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Tipologia impegno ammesso" )
     * @RicercaFormType( ordine = 3, type = "choice", label = "Tipologia impegno ammesso", options={"placeholder":"-", "choices":{"I":"Impegno", "DI":"Disimpegno", "I-TR":"Impegno per trasferimento","D-TR":"Diseimpegno per trasferimento"}})
     */
    protected $tipologia_imp_amm;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 10, titolo="Importo impegno ammesso" )
     * @RicercaFormType( ordine = 10, type = "moneta", label = "Importo impegno ammesso")
     */
    protected $importo_imp_amm;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 11, titolo="Note impegno", show=false )
     * @RicercaFormType( ordine = 11, type = "text", label = "Note impegno")
     */
    protected $note_imp;

   
   

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
    public function getTc36LivelloGerarchico()
    {
        return $this->tc36_livello_gerarchico;
    }

    /**
     * @param mixed $tc36_livello_gerarchico
     */
    public function setTc36LivelloGerarchico($tc36_livello_gerarchico)
    {
        $this->tc36_livello_gerarchico = $tc36_livello_gerarchico;
    }

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
    public function getDataImpAmm()
    {
        return $this->data_imp_amm;
    }

    /**
     * @param mixed $data_imp_amm
     */
    public function setDataImpAmm($data_imp_amm)
    {
        $this->data_imp_amm = $data_imp_amm;
    }

    /**
     * @return mixed
     */
    public function getTipologiaImpAmm()
    {
        return $this->tipologia_imp_amm;
    }

    /**
     * @param mixed $tipologia_imp_amm
     */
    public function setTipologiaImpAmm($tipologia_imp_amm)
    {
        $this->tipologia_imp_amm = $tipologia_imp_amm;
    }

    /**
     * @return mixed
     */
    public function getImportoImpAmm()
    {
        return $this->importo_imp_amm;
    }

    /**
     * @param mixed $importo_imp_amm
     */
    public function setImportoImpAmm($importo_imp_amm)
    {
        $this->importo_imp_amm = $importo_imp_amm;
    }

    /**
     * @return mixed
     */
    public function getNoteImp()
    {
        return $this->note_imp;
    }

    /**
     * @param mixed $note_imp
     */
    public function setNoteImp($note_imp)
    {
        $this->note_imp = $note_imp;
    }

}
