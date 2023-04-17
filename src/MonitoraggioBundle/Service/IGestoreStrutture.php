<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 22/06/17
 * Time: 11:13
 */


namespace MonitoraggioBundle\Service;

use MonitoraggioBundle\Entity\ElencoStruttureProtocollo;
use Symfony\Component\DependencyInjection\ContainerInterface;


interface IGestoreStrutture {

    public function __construct(ContainerInterface $container, ElencoStruttureProtocollo $struttura = null);

    // const CODICE_TRACCIATO = "";

    // public function getTracciato();

}
