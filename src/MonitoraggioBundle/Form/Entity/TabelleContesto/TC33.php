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
 * Description of TC9
 *
 * @author lfontana
 */
class TC33 extends Base{
    /**
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice fondo" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice fondo")
     */
    protected $cod_fondo;

    /**
     * @var string
     * @ViewElenco( ordine = 2, titolo="Descrizione fondo" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione fondo")
     */
    protected $descrizione_fondo;

    /**
     * @var string
     * @ViewElenco( ordine = 3, titolo="Codice fonte" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Codice fonte")
     */
    protected $cod_fonte;

    /**
     * @var string
     * @ViewElenco( ordine = 4, titolo="Descrizione fonte" )
     * @RicercaFormType( ordine = 4, type = "text", label = "Descrizione fonte")
     */
    protected $descrizione_fonte;

    
    /**
     * @return mixed
     */
    public function getCodFondo()
    {
        return $this->cod_fondo;
    }

    /**
     * @param mixed $cod_fondo
     */
    public function setCodFondo($cod_fondo)
    {
        $this->cod_fondo = $cod_fondo;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneFondo()
    {
        return $this->descrizione_fondo;
    }

    /**
     * @param mixed $descrizione_fondo
     */
    public function setDescrizioneFondo($descrizione_fondo)
    {
        $this->descrizione_fondo = $descrizione_fondo;
    }

    /**
     * @return mixed
     */
    public function getCodFonte()
    {
        return $this->cod_fonte;
    }

    /**
     * @param mixed $cod_fonte
     */
    public function setCodFonte($cod_fonte)
    {
        $this->cod_fonte = $cod_fonte;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneFonte()
    {
        return $this->descrizione_fonte;
    }

    /**
     * @param mixed $descrizione_fonte
     */
    public function setDescrizioneFonte($descrizione_fonte)
    {
        $this->descrizione_fonte = $descrizione_fonte;
    }


}
