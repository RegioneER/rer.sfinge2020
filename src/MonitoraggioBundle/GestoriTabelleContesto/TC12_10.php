<?php
namespace MonitoraggioBundle\GestoriTabelleContesto;
use MonitoraggioBundle\Service\GestoreTabelleContestoBase;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TC12_8
 *
 * @author lfontana
 */
class TC12_10 extends GestoreTabelleContestoBase{
    
    protected function getDefaultOptions() {
        return array(
            'programmi' => $this->getEm()->getRepository('MonitoraggioBundle:TC4Programma')->findAll(),
        );
    }

}
