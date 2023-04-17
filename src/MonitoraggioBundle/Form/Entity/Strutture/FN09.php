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
class FN09 extends BaseRicercaStruttura{

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
     * @ViewElenco( ordine = 3, titolo="Domanda di pagamento", show = false )
     * @RicercaFormType( ordine = 3, type = "entity", label = "Domanda di pagamento", options={"class": "MonitoraggioBundle\Entity\TC41DomandaPagamento"})
     */
     protected $tc41_domande_pagamento;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Livello gerarchico", show = false )
     * @RicercaFormType( ordine = 5, type = "text", label = "Livello gerarchico")
     */
    protected $tc36_livello_gerarchico;

   
     /**
     *
     * @var string
     * @ViewElenco( ordine = 7, titolo="Data domanda" )
     * @RicercaFormType( ordine = 7, type = "birthday", label = "Data domanda", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
    protected $data_domanda;

     /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Tipologia pagamento" )
     * @RicercaFormType( ordine = 3, type = "choice", label = "Tipologia pagamento", options={"placeholder":"-", "choices":{"C":"Certificato", "D":"Decertificato"}})
     */
    protected $tipologia_importo;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 6, titolo="Importo totale" )
     * @RicercaFormType( ordine = 6, type = "moneta", label = "Importo spesa totale")
     */
    protected $importo_spesa_tot;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 7, titolo="Importo spese ammissibili" )
     * @RicercaFormType( ordine = 7, type = "text", label = "Importo spese ammissibili")
     */
    protected $importo_spesa_pub;

    
    /**
     * @return mixed
     */
    public function getTc41DomandePagamento()
    {
        return $this->tc41_domande_pagamento;
    }

    /**
     * @param mixed $tc41_domande_pagamento
     */
    public function setTc41DomandePagamento($tc41_domande_pagamento)
    {
        $this->tc41_domande_pagamento = $tc41_domande_pagamento;
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
    public function getDataDomanda()
    {
        return $this->data_domanda;
    }

    /**
     * @param mixed $data_domanda
     */
    public function setDataDomanda($data_domanda)
    {
        $this->data_domanda = $data_domanda;
    }

    /**
     * @return mixed
     */
    public function getTipologiaImporto()
    {
        return $this->tipologia_importo;
    }

    /**
     * @param mixed $tipologia_importo
     */
    public function setTipologiaImporto($tipologia_importo)
    {
        $this->tipologia_importo = $tipologia_importo;
    }

    /**
     * @return mixed
     */
    public function getImportoSpesaTot()
    {
        return $this->importo_spesa_tot;
    }

    /**
     * @param mixed $importo_spesa_tot
     */
    public function setImportoSpesaTot($importo_spesa_tot)
    {
        $this->importo_spesa_tot = $importo_spesa_tot;
    }

    /**
     * @return mixed
     */
    public function getImportoSpesaPub()
    {
        return $this->importo_spesa_pub;
    }

    /**
     * @param mixed $importo_spesa_pub
     */
    public function setImportoSpesaPub($importo_spesa_pub)
    {
        $this->importo_spesa_pub = $importo_spesa_pub;
    }


}
