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
class FN01 extends BaseRicercaStruttura{

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
     * @ViewElenco( ordine = 2, titolo="Programma" )
     * @RicercaFormType( ordine = 2, type = "entity", label = "Programma", options={"class": "MonitoraggioBundle\Entity\TC4Programma"})
     */
    protected $tc4_programma;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Livello Gerarchico" )
     * @RicercaFormType( ordine = 3, type = "entity", label = "Livello Gerarchico", options={"class": "MonitoraggioBundle\Entity\TC36LivelloGerarchico"})
     */
    protected $tc36_livello_gerarchico;

   

     /**
     *
     * @var string
     * @ViewElenco( ordine = 6, titolo="Importo ammesso" )
     * @RicercaFormType( ordine =6 , type = "moneta", label = "Importo ammesso")
     */
    protected $importo_ammesso;

   
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
    public function getImportoAmmesso()
    {
        return $this->importo_ammesso;
    }

    /**
     * @param mixed $importo_ammesso
     */
    public function setImportoAmmesso($importo_ammesso)
    {
        $this->importo_ammesso = $importo_ammesso;
    }

  

}
