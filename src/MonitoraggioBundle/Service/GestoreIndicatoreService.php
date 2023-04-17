<?php

namespace MonitoraggioBundle\Service;

use BaseBundle\Service\BaseService;
use RichiesteBundle\Entity\Richiesta;

class GestoreIndicatoreService extends BaseService {
    
    const SERVICE_NAMESPACE = 'MonitoraggioBundle\Service\GestoriIndicatori';
    const ROOT_NAME = 'GestoreIndicatori_';

    public function getGestore(Richiesta $richiesta): IGestoreIndicatoreOutput {
        if(!$richiesta->getFlagPor()){
            return new GestoreIndicatoreOutputDummy($this->container, $richiesta);
        }
        $className = self::SERVICE_NAMESPACE .'\\'. self::ROOT_NAME . $richiesta->getProcedura()->getId();
        if(\class_exists($className)){
            return new $className($this->container, $richiesta);
        }

        return new GestoreIndicatoreOutputBase($this->container, $richiesta);
    }
}
