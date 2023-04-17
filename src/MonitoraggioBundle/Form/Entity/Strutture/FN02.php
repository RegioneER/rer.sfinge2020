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
class FN02 extends BaseRicercaStruttura{

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
     * @ViewElenco( ordine = 2, titolo="Voce spesa" )
     * @RicercaFormType( ordine = 2, type = "entity", label = "Voce spesa", options={"class": "MonitoraggioBundle\Entity\TC4Programma"})
     */
    protected $tc37_voce_spesa;

    

     /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Importo" )
     * @RicercaFormType( ordine = 3, type = "moneta", label = "Importo")
     */
    protected $importo;

   
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
    public function getTc37VoceSpesa()
    {
        return $this->tc37_voce_spesa;
    }

    /**
     * @param mixed $tc37_voce_spesa
     */
    public function setTc37VoceSpesa($tc37_voce_spesa)
    {
        $this->tc37_voce_spesa = $tc37_voce_spesa;
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
