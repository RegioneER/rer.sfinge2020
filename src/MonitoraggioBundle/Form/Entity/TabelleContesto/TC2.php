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
 * Description of TC2
 *
 * @author lfontana
 */
class TC2 extends Base{
    
/**
 *
 * @var int
 * @RicercaFormType(type="integer", label="Codice della Tipologia della Procedura di Attivazione")
 * @ViewElenco( titolo="Codice", ordine = 1)
 */
    protected $tip_procedura_att;
    /**
     * @RicercaFormType(type="text", label="Descrizione della tipologia di Procedura")
     * @ViewElenco( titolo="Descrizione", ordine = 2)
     * @var string
     */
    protected $cod_proc_att_locale;
    
      public function getTipProceduraAtt()
    {
        return $this->tip_procedura_att;
    }

    /**
     * @param mixed $tip_procedura_att
     */
    public function setTipProceduraAtt($tip_procedura_att)
    {
        $this->tip_procedura_att = $tip_procedura_att;
    }

    /**
     * @return string
     */
    public function getCodProcAttLocale()
    {
        return $this->cod_proc_att_locale;
    }

    /**
     * @param string $cod_proc_att_locale
     */
    public function setCodProcAttLocale($cod_proc_att_locale)
    {
        $this->cod_proc_att_locale = $cod_proc_att_locale;
    }

    

}
