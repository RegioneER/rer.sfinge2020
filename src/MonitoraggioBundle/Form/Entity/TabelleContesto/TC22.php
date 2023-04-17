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
class TC22 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice motivo assenza CIG")
      * @ViewElenco( ordine = 1, titolo="Codice motivo assenza CIG" )
     */
    protected $motivo_assenza_cig;

   /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "textarea", label = "Descrizione motivo assenza CIG")
      * @ViewElenco( ordine = 2, titolo="Descrizione motivo assenza CIG" )
     */
    protected $desc_motivo_assenza_cig;

    /**
     * @return mixed
     */
    public function getMotivoAssenzaCig()
    {
        return $this->motivo_assenza_cig;
    }

    /**
     * @param mixed $motivo_assenza_cig
     */
    public function setMotivoAssenzaCig($motivo_assenza_cig)
    {
        $this->motivo_assenza_cig = $motivo_assenza_cig;
    }

    /**
     * @return mixed
     */
    public function getDescMotivoAssenzaCig()
    {
        return $this->desc_motivo_assenza_cig;
    }

    /**
     * @param mixed $desc_motivo_assenza_cig
     */
    public function setDescMotivoAssenzaCig($desc_motivo_assenza_cig)
    {
        $this->desc_motivo_assenza_cig = $desc_motivo_assenza_cig;
    }


}
