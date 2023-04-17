<?php

namespace MonitoraggioBundle\Service;

use BaseBundle\Service\BaseService;
use MonitoraggioBundle\Service\GestoriImpegni\Dummy;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Service\GestoriImpegni\Privato;
use MonitoraggioBundle\Service\GestoriImpegni\Pubblico;

class GestoreImpegniService extends BaseService {
    public function getGestore(Richiesta $richiesta): IGestoreImpegni {
        if(! $richiesta->getFlagPor()){
            return new Dummy($this->container, $richiesta);
        }
        
        if ($richiesta->getMonPrgPubblico()) {
            return new Pubblico($this->container, $richiesta);
        }

        return new Privato($this->container, $richiesta);
    }
}
