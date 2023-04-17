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
 * @author lfontana
 */
class TC21 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice qualifica")
      * @ViewElenco( ordine = 1, titolo="Codice qualifica" )
     */
      protected $cod_qualifica;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione qualifica")
      * @ViewElenco( ordine = 2, titolo="Descrizione qualifica" )
     */
    protected $descrizione_qualifica;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 3, type = "text", label = "Codice amministrazione")
     * @ViewElenco( ordine = 3, titolo="Codice amministrazione" )
     */
    protected $cod_amministrazione;

    /**
     * @return mixed
     */
    public function getCodQualifica()
    {
        return $this->cod_qualifica;
    }

    /**
     * @param mixed $cod_qualifica
     */
    public function setCodQualifica($cod_qualifica)
    {
        $this->cod_qualifica = $cod_qualifica;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneQualifica()
    {
        return $this->descrizione_qualifica;
    }

    /**
     * @param mixed $descrizione_qualifica
     */
    public function setDescrizioneQualifica($descrizione_qualifica)
    {
        $this->descrizione_qualifica = $descrizione_qualifica;
    }

    /**
     * @return mixed
     */
    public function getCodAmministrazione()
    {
        return $this->cod_amministrazione;
    }

    /**
     * @param mixed $cod_amministrazione
     */
    public function setCodAmministrazione($cod_amministrazione)
    {
        $this->cod_amministrazione = $cod_amministrazione;
    }



}
