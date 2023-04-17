<?php

namespace MonitoraggioBundle\Form\Entity\TabelleContesto;

/**
 * Description of TC1
 *
 * @author lfontana
 */

class TC1 extends Base{
     
    protected $cod_proc_att_locale; // codice locale

    protected $tip_procedura_att;

    /*var string
     */
    protected $descr_procedura_att;
    
    /**
     *
     * @var  string
     */
    protected $stato;

    public function getCodProcAttLocale() {
        return $this->cod_proc_att_locale;
    }

    public function getTipProceduraAtt() {
        return $this->tip_procedura_att;
    }

    public function getDescrProceduraAtt() {
        return $this->descr_procedura_att;
    }
       
    public function getStato() {
        return $this->stato;
    }

    public function setCodProcAttLocale($cod_proc_att_locale) {
        $this->cod_proc_att_locale = $cod_proc_att_locale;
    }

    public function setTipProceduraAtt($tip_procedura_att) {
        $this->tip_procedura_att = $tip_procedura_att;
    }

    public function setDescrProceduraAtt($descr_procedura_att) {
        $this->descr_procedura_att = $descr_procedura_att;
    }
    
    public function setStato($stato) {
        $this->stato = $stato;
    }

}
