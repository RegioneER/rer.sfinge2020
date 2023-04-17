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
class TC26 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Codice ATECO")
      * @ViewElenco( ordine = 1, titolo="Codice ATECO" )
     */
   protected $cod_ateco_anno;

   /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione")
      * @ViewElenco( ordine = 2, titolo="Descrizione" )
     */
    protected $descrizione_codice_ateco;

       /**
     * @return mixed
     */
    public function getCodAtecoAnno()
    {
        return $this->cod_ateco_anno;
    }

    /**
     * @param mixed $cod_ateco_anno
     */
    public function setCodAtecoAnno($cod_ateco_anno)
    {
        $this->cod_ateco_anno = $cod_ateco_anno;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneCodiceAteco()
    {
        return $this->descrizione_codice_ateco;
    }

    /**
     * @param mixed $descrizione_codice_ateco
     */
    public function setDescrizioneCodiceAteco($descrizione_codice_ateco)
    {
        $this->descrizione_codice_ateco = $descrizione_codice_ateco;
    }

}
