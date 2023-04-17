<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\GestoriTabelleContesto;
use MonitoraggioBundle\Service\GestoreTabelleContestoBase;

/**
 * Description of TC8
 *
 * @author lfontana
 */
class TC8 extends GestoreTabelleContestoBase{
    public function getElenco(array $formOptions = array() ) {
                
        return parent::getElenco(array_merge($formOptions, array(
            'programmi' => $this->getEm()->getRepository('MonitoraggioBundle:TC4Programma')->findAll(),
        )));
    }
    
    public function inserisciElemento(array $formOptions = array(), array $twigOptions = array() ) {
        
        return parent::inserisciElemento(
                array_merge(array(
                    'programmi' => $this->getEm()->getRepository('MonitoraggioBundle:TC4Programma')->findAll(),
                ),$formOptions)
        ,$twigOptions);
    }
    
    public function modificaElemento($recordId, array $formOptions = array(), array $twigOptions = array() ) {
        return parent::modificaElemento( $recordId,
                array_merge(array(
                    'programmi' => $this->getEm()->getRepository('MonitoraggioBundle:TC4Programma')->findAll(),
                ),$formOptions)
        ,$twigOptions);
    }
    
    public function visualizzaElemento($recordId, array $formOptions = array(), array $twigOptions = array() ) {
        return parent::visualizzaElemento( $recordId,
                array_merge(array(
                    'programmi' => $this->getEm()->getRepository('MonitoraggioBundle:TC4Programma')->findAll(),
                ),$formOptions)
        ,$twigOptions);
    }
}
