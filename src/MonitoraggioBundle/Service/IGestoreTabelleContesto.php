<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Service;

use MonitoraggioBundle\Entity\ElencoTabelleContesto;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Description of IGestoreTabelleContesto
 *
 * @author lfontana
 */
interface IGestoreTabelleContesto {
    
    public function __construct(ContainerInterface $container, ElencoTabelleContesto $tabella = null);
    
    public function getElenco(array $formOptions = array() );
    
    public function pulisciElenco();
    
    public function inserisciElemento(array $formOptions = array(), array $twigOptions = array());
    
    public function visualizzaElemento($recordId, array $formOptions = array(), array $twigOptions = array() );
    
    public function modificaElemento($recordId, array $formOptions = array(), array $twigOptions = array() );
            
}
