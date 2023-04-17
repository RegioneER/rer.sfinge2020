<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Service;

use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Description of IGestoreTabelleContesto
 *
 * @author lfontana
 */
interface IGestorePianoCosto {
    
    public function __construct(ContainerInterface $container, Richiesta $tabella );
    
    /**
    * @return array Torna un array contenenti gli anni del piano costo. Per ogni anno è presente un array con le singole voci
    */
    public function generaArrayPianoCostoTotaleRealizzato(): iterable;
}
