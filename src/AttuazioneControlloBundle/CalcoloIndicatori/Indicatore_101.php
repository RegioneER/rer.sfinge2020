<?php

namespace AttuazioneControlloBundle\CalcoloIndicatori;

use AttuazioneControlloBundle\Service\ACalcoloIndicatore;

class Indicatore_101 extends ACalcoloIndicatore {
    public function getValore(): float {
        return $this->richiesta->getProponenti()->count();
    }
}
