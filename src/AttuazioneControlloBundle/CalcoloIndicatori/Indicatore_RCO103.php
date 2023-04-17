<?php

namespace AttuazioneControlloBundle\CalcoloIndicatori;

use AttuazioneControlloBundle\Service\ACalcoloIndicatore;

class Indicatore_RCO103 extends ACalcoloIndicatore {

    public function getValore(): float {
        $richiesta = $this->richiesta;
        $procedura = $richiesta->getProcedura();
        $count = 0;
        foreach ($richiesta->getProponenti() as $proponente) {
            $hasForteCrescita = $this->container->get("gestore_proponenti")->getGestore($procedura)->getForteCrescita($proponente);
            
            if($hasForteCrescita) {
                $count++;
            }
        }
        return $count;
    }
  

}
