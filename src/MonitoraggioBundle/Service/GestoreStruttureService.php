<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 23/06/17
 * Time: 15:58
 */


namespace MonitoraggioBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use MonitoraggioBundle\Entity\ElencoStruttureProtocollo;

/**
 *
 */
class GestoreStruttureService {

    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @param ElencoStruttureProtocollo|null $struttura
     * @return object|
     * @throws \Exception
     */

    public function getGestore(ElencoStruttureProtocollo $struttura = null){

        $classe = '\MonitoraggioBundle\GestoriStrutture\\'. (is_null($struttura) ? 'Elenco' : $struttura->getCodice());
        if(class_exists($classe)){
            return new $classe($this->container, $struttura);
        }

        return new GestoreStruttureBase($this->container, $struttura);
    }
}
