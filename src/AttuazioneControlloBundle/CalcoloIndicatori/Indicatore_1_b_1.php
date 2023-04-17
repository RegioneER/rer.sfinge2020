<?php

namespace AttuazioneControlloBundle\CalcoloIndicatori;

use AttuazioneControlloBundle\Service\ACalcoloIndicatore;

class Indicatore_1_b_1 extends ACalcoloIndicatore {
    public function getValore(): float {
        return $this->richiesta->getProponenti()->count();
    }
}
