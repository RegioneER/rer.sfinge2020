<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Entity\TabelleContesto;

use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;
/**
/**
 * Description of TC16
 *
 * @author lfontana
 */
class TC19 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice criterio selezione")
      * @ViewElenco( ordine = 1, titolo="Codice criterio selezione" )
     */
     protected $cod_criterio_selezione;

   /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "textarea", label = "Descrizione criterio selezione")
      * @ViewElenco( ordine = 2, titolo="Descrizione criterio selezione" )
     */
    protected $descrizione_criterio_selezione;


    /**
     * @return mixed
     */
    public function getCodCriterioSelezione()
    {
        return $this->cod_criterio_selezione;
    }

    /**
     * @param mixed $cod_criterio_selezione
     */
    public function setCodCriterioSelezione($cod_criterio_selezione)
    {
        $this->cod_criterio_selezione = $cod_criterio_selezione;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneCriterioSelezione()
    {
        return $this->descrizione_criterio_selezione;
    }

    /**
     * @param mixed $descrizione_criterio_selezione
     */
    public function setDescrizioneCriterioSelezione($descrizione_criterio_selezione)
    {
        $this->descrizione_criterio_selezione = $descrizione_criterio_selezione;
    }



}
