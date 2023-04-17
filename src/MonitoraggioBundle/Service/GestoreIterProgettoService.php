<?php

namespace MonitoraggioBundle\Service;

use BaseBundle\Service\BaseService;
use RichiesteBundle\Entity\Richiesta;

class GestoreIterProgettoService extends BaseService {

    const SERVICE_NAMESPACE = 'MonitoraggioBundle\Service\GestoriIterProgetto';
    const ROOT_NAME = 'GestoreIterProgetto_';

    public function getIstanza(Richiesta $richiesta): IGestoreIterProgetto {

        $className = self::SERVICE_NAMESPACE . '\\' . self::ROOT_NAME . $richiesta->getProcedura()->getId();

        if (\class_exists($className)) {
            return new $className($this->container, $richiesta);
        }

        return new GestoreIterProgettoBase($this->container, $richiesta);
    }

}
