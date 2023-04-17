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
class TC48 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Codice tipologia procedura attivazione")
      * @ViewElenco( ordine = 1, titolo="Codice tipologia procedura attivazione" )
     */
   protected $tip_proc_att_orig;

     /**
     *
     * @var string
        * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione tipologia procedura attivazione")
      * @ViewElenco( ordine = 2, titolo="Descrizione tipologia procedura attivazione" )
     */
    protected $descrizione_tipo_procedura_orig;

    
    /**
     * @return mixed
     */
    public function getTipProcAttOrig()
    {
        return $this->tip_proc_att_orig;
    }

    /**
     * @param mixed $tip_proc_att_orig
     */
    public function setTipProcAttOrig($tip_proc_att_orig)
    {
        $this->tip_proc_att_orig = $tip_proc_att_orig;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneTipoProceduraOrig()
    {
        return $this->descrizione_tipo_procedura_orig;
    }

    /**
     * @param mixed $descrizione_tipo_procedura_orig
     */
    public function setDescrizioneTipoProceduraOrig($descrizione_tipo_procedura_orig)
    {
        $this->descrizione_tipo_procedura_orig = $descrizione_tipo_procedura_orig;
    }


}
