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
 * Description of TC3
 *
 * @author lfontana
 */
class TC3 extends Base{
    
    /**
     * @RicercaFormType(type="integer", label="Codice", ordine=1)
     * @ViewElenco( titolo="Codice", ordine = 1)
     * @var integer 
     */
    protected $cod_tipo_resp_proc;
    
    /**
     * @RicercaFormType(type="text", label="Descrizione", ordine=2)
     * @ViewElenco(titolo = "Descrizione", ordine = 2)
     * @var string 
     */
    protected $descrizione_responsabile_procedura;
    
    public function getCodTipoRespProc() {
        return $this->cod_tipo_resp_proc;
    }

    public function getDescrizioneResponsabileProcedura() {
        return $this->descrizione_responsabile_procedura;
    }

    public function setCodTipoRespProc($cod_tipo_resp_proc) {
        $this->cod_tipo_resp_proc = $cod_tipo_resp_proc;
    }

    public function setDescrizioneResponsabileProcedura($descrizione_responsabile_procedura) {
        $this->descrizione_responsabile_procedura = $descrizione_responsabile_procedura;
    }
}
