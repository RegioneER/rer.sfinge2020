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
class FN10 extends BaseRicercaStruttura{

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
     * @RicercaFormType( ordine = 3, type = "entity", label = "Domanda di pagamento", options={"class": "MonitoraggioBundle\Entity\TC33FonteFinanziaria"})
     */
    protected $tc33_fonte_finanziaria;

   


    /**
     *
     * @var string
     * @ViewElenco( ordine = 6, titolo="Importo" )
     * @RicercaFormType( ordine = 6, type = "moneta", label = "Importo")
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



}
