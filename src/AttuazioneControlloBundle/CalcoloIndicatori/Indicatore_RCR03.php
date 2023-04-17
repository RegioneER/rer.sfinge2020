<?php

namespace AttuazioneControlloBundle\CalcoloIndicatori;

use AttuazioneControlloBundle\Service\ACalcoloIndicatore;

class Indicatore_RCR03 extends ACalcoloIndicatore {

    public function getValore(): float {
        $richiesta = $this->richiesta;
        $procedura = $richiesta->getProcedura();
        $count = 0;
        foreach ($richiesta->getProponenti() as $proponente) {
            $hasInnovazioneProcesso = $this->container->get("gestore_proponenti")->getGestore($procedura)->getInnovazioneProcesso($proponente);
            $hasInnovazioneProdotto = $this->container->get("gestore_proponenti")->getGestore($procedura)->getInnovazioneProdotto($proponente);
            
            if($hasInnovazioneProcesso || $hasInnovazioneProdotto) {
                $count++;
            }
        }
        return $count;
    }
  

}
