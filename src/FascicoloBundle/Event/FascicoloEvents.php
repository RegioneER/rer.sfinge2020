<?php

namespace FascicoloBundle\Event;

/**
 * Description of FascicoloEvents
 *
 * @author aturdo
 */
final class FascicoloEvents {
    /**
     * L'evento fascicolo.istanza è lanciato ogni volta si tenta di accedere ad 
	 * una istanza di un fascicolo.
     *
     * Il listener dell'evento riceve l'istanza FascicoloBundle\Event\IstanzaFascicoloEvent 
	 * del fascicolo a cui si tenta di  accedere.
     *
     * @var string
     */
    const FASCICOLO_ISTANZA = 'fascicolo.istanza';
}
