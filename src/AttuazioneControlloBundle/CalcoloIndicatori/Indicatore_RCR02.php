<?php

namespace AttuazioneControlloBundle\CalcoloIndicatori;

use AttuazioneControlloBundle\Service\ACalcoloIndicatore;

class Indicatore_RCR02 extends ACalcoloIndicatore {
    public function getValore(): float {
        if(!is_null($this->richiesta->getAttuazioneControllo()) && $this->richiesta->hasPagamentoSaldo()) {
            return $this->richiesta->getTotalePagato();
        } elseif(!is_null($this->richiesta->getIstruttoria())) {
            return $this->richiesta->getCostoAmmesso();
        }
        return $this->richiesta->getTotalePianoCosto();
    }
}
