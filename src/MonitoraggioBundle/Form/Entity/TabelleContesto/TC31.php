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
 * Description of TC17
 *
 * @author lfontana
 */
class TC31 extends Base{
 
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice gruppo vulnerabile")
     * @ViewElenco( ordine = 1, titolo="Codice gruppo vulnerabile" )
     */
    protected $codice_vulnerabile_pa;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione gruppo vulnerabile")
     * @ViewElenco( ordine = 2, titolo="Descrizione gruppo vulnerabile" )
     */
    protected $descr_vulnerabile_pa;

    /**
     * @return mixed
     */
    public function getCodiceVulnerabilePa()
    {
        return $this->codice_vulnerabile_pa;
    }

    /**
     * @param mixed $codice_vulnerabile_pa
     */
    public function setCodiceVulnerabilePa($codice_vulnerabile_pa)
    {
        $this->codice_vulnerabile_pa = $codice_vulnerabile_pa;
    }

    /**
     * @return mixed
     */
    public function getDescrVulnerabilePa()
    {
        return $this->descr_vulnerabile_pa;
    }

    /**
     * @param mixed $descr_vulnerabile_pa
     */
    public function setDescrVulnerabilePa($descr_vulnerabile_pa)
    {
        $this->descr_vulnerabile_pa = $descr_vulnerabile_pa;
    }



}
