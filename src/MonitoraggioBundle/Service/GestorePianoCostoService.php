<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 23/06/17
 * Time: 15:58
 */


namespace MonitoraggioBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;

/**
 *
 */
class GestorePianoCostoService {
    const NAMESPACE_GESTORE = '\MonitoraggioBundle\GestoriPianoCosto\\';
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @param \RichiesteBundle\Entity\Richiesta $richiesta
     * @return object|
     * @throws \Exception
     */

    public function getGestore(Richiesta $richiesta){
        
        $classe = self::NAMESPACE_GESTORE . 'PianoCosto'. $richiesta->getProcedura()->getId();
        if(class_exists($classe)){
            return new $classe($this->container, $richiesta);
        }
        $classe = self::NAMESPACE_GESTORE . 'PianoCostoGenerico';
        return new $classe($this->container, $richiesta);
    }
}
