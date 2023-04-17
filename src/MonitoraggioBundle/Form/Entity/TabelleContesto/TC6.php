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
 * Description of TC6
 *
 * @author lfontana
 */
class TC6 extends Base {
    /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice tipo aiuto" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice tipo aiuto")
     */
    protected $tipo_aiuto;
    
    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Descrizione tipo aiuto" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione tipo aiuto")
     */
    protected $descrizione_tipo_aiuto;
    
    public function getTipoAiuto() {
        return $this->tipo_aiuto;
    }

    public function getDescrizioneTipoAiuto() {
        return $this->descrizione_tipo_aiuto;
    }

    public function setTipoAiuto($tipo_aiuto) {
        $this->tipo_aiuto = $tipo_aiuto;
    }

    public function setDescrizioneTipoAiuto($descrizione_tipo_aiuto) {
        $this->descrizione_tipo_aiuto = $descrizione_tipo_aiuto;
    }


}
