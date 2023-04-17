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
class FN07 extends BaseRicercaStruttura{

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
     * @ViewElenco( ordine = 15, titolo="Causale", show = false )
     * @RicercaFormType( ordine = 15, type = "entity", label = "Causale", options={"class": "MonitoraggioBundle\Entity\TC39CausalePagamento"})
     */
     protected $tc39_causale_pagamento;

   
    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Codice pagamento" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Codice pagamento")
     */
    protected $cod_pagamento;


   /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Tipologia pagamento" )
     * @RicercaFormType( ordine = 3, type = "choice", label = "Tipologia pagamento", options={"placeholder":"-", "choices":{"P":"Pagamento", "R":"Rettifica", "P-TR":"Pagamento per trasferimento","R-TR":"Rettifica per trasferimento"}})
     */
    protected $tipologia_pag;

   /**
     *
     * @var string
     * @ViewElenco( ordine = 7, titolo="Data pagamento" )
     * @RicercaFormType( ordine = 7, type = "birthday", label = "Data pagamento", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
    protected $data_pagamento;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 10, titolo="Importo" )
     * @RicercaFormType( ordine = 10, type = "moneta", label = "Importo pagamento")
     */
    protected $importo_pag_amm;

   /**
     *
     * @var string
     * @ViewElenco( ordine = 19, titolo="Note", show = false )
     * @RicercaFormType( ordine = 19, type = "text", label = "Note pagamento")
     */
    protected $note_pag;

   
    /**
     *
     * @var string
     * @ViewElenco( ordine = 15, titolo="Programma", show = false )
     * @RicercaFormType( ordine = 15, type = "entity", label = "Programma", options={"class": "MonitoraggioBundle\Entity\TC4Programma"})
     */
     protected $tc4_programma;
    
    
    
    /**
     * @return mixed
     */
    public function getTc39CausalePagamento()
    {
        return $this->tc39_causale_pagamento;
    }

    /**
     * @param mixed $tc39_causale_pagamento
     */
    public function setTc39CausalePagamento($tc39_causale_pagamento)
    {
        $this->tc39_causale_pagamento = $tc39_causale_pagamento;
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
    public function getCodPagamento()
    {
        return $this->cod_pagamento;
    }

    /**
     * @param mixed $cod_pagamento
     */
    public function setCodPagamento($cod_pagamento)
    {
        $this->cod_pagamento = $cod_pagamento;
    }

    /**
     * @return mixed
     */
    public function getTipologiaPag()
    {
        return $this->tipologia_pag;
    }

    /**
     * @param mixed $tipologia_pag
     */
    public function setTipologiaPag($tipologia_pag)
    {
        $this->tipologia_pag = $tipologia_pag;
    }

    /**
     * @return mixed
     */
    public function getDataPagamento()
    {
        return $this->data_pagamento;
    }

    /**
     * @param mixed $data_pagamento
     */
    public function setDataPagamento($data_pagamento)
    {
        $this->data_pagamento = $data_pagamento;
    }

   /**
     * @return mixed
     */
    public function getImportoPagAmm()
    {
        return $this->importo_pag_amm;
    }

    /**
     * @param mixed $importo_pag_amm
     */
    public function setImportoPagAmm($importo_pag_amm)
    {
        $this->importo_pag_amm = $importo_pag_amm;
    }


    /**
     * @return mixed
     */
    public function getNotePag()
    {
        return $this->note_pag;
    }

    /**
     * @param mixed $note_pag
     */
    public function setNotePag($note_pag)
    {
        $this->note_pag = $note_pag;
    }
    
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


}
