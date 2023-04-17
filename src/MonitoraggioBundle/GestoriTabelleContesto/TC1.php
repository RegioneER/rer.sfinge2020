<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\GestoriTabelleContesto;

use MonitoraggioBundle\Service\GestoreTabelleContestoBase;
/**
 * Description of TC1
 *
 * @author lfontana
 */
class TC1 extends GestoreTabelleContestoBase {
    public function getElenco(array $formOptions = array() ) {
        $em = $this->getEm();
        
        return parent::getElenco(array_merge($formOptions, array(
            'tipiProcedura' => $em->getRepository('MonitoraggioBundle:TC2TipoProceduraAttivazione')->findAll(),
        )));
    }
    
    public function inserisciElemento(array $formOptions = array(), array $twigOptions = array() ) {
        return parent::inserisciElemento(
                array_merge(array(
                    'procedureOperative' => $this->getEm()->getRepository('SfingeBundle:Procedura')->findAll(),
                    'tipiProcedureAttivazione' => $this->getEm()->getRepository('MonitoraggioBundle:TC2TipoProceduraAttivazione')->findAll(),
                    'responsabiliProcedure' => $this->getEm()->getRepository('MonitoraggioBundle:TC3ResponsabileProcedura')->findAll(),
                ),$formOptions)
        ,$twigOptions);
    }
    
    public function modificaElemento($recordId, array $formOptions = array(), array $twigOptions = array() ) {
        return parent::modificaElemento( $recordId,
                array_merge(array(
                    'procedureOperative' => $this->getEm()->getRepository('SfingeBundle:Procedura')->findAll(),
                    'tipiProcedureAttivazione' => $this->getEm()->getRepository('MonitoraggioBundle:TC2TipoProceduraAttivazione')->findAll(),
                    'responsabiliProcedure' => $this->getEm()->getRepository('MonitoraggioBundle:TC3ResponsabileProcedura')->findAll(),
                ),$formOptions)
        ,$twigOptions);
    }
    
    public function visualizzaElemento($recordId, array $formOptions = array(), array $twigOptions = array() ){
        return parent::visualizzaElemento( $recordId,
                array_merge(array(
                    'procedureOperative' => $this->getEm()->getRepository('SfingeBundle:Procedura')->findAll(),
                    'tipiProcedureAttivazione' => $this->getEm()->getRepository('MonitoraggioBundle:TC2TipoProceduraAttivazione')->findAll(),
                    'responsabiliProcedure' => $this->getEm()->getRepository('MonitoraggioBundle:TC3ResponsabileProcedura')->findAll(),
                ),$formOptions)
        ,$twigOptions);
    }
}
