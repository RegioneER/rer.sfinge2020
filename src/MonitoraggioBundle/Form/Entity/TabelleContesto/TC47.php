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
class TC47 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Codice stato progetto")
      * @ViewElenco( ordine = 1, titolo="Codice stato progetto" )
     */
   protected $stato_progetto;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione stato progetto")
      * @ViewElenco( ordine = 2, titolo="Descrizione stato progetto" )
     */
    protected $descr_stato_prg;

    /**
     * @return mixed
     */
    public function getStatoProgetto()
    {
        return $this->stato_progetto;
    }

    /**
     * @param mixed $stato_progetto
     */
    public function setStatoProgetto($stato_progetto)
    {
        $this->stato_progetto = $stato_progetto;
    }

    /**
     * @return mixed
     */
    public function getDescrStatoPrg()
    {
        return $this->descr_stato_prg;
    }

    /**
     * @param mixed $descr_stato_prg
     */
    public function setDescrStatoPrg($descr_stato_prg)
    {
        $this->descr_stato_prg = $descr_stato_prg;
    }




}
