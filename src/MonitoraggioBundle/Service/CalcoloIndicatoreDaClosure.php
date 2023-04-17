<?php

namespace MonitoraggioBundle\Service;

use AttuazioneControlloBundle\Service\ACalcoloIndicatore;

class CalcoloIndicatoreDaClosure extends ACalcoloIndicatore
{
    /**
     * @var \Closure
     */
    protected $closure;

    public function setClosure(\Closure $closure): void
    {
        $this->closure = $closure;
    }

    public function getValore(): float{
        return $this->closure->call($this, $this->richiesta);
    }
}