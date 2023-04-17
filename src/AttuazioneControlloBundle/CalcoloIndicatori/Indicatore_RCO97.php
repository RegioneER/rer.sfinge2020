<?php

namespace AttuazioneControlloBundle\CalcoloIndicatori;

use AttuazioneControlloBundle\Service\ACalcoloIndicatore;

class Indicatore_RCO97 extends ACalcoloIndicatore {
    public function getValore(): float {
        return 1;
    }
}
