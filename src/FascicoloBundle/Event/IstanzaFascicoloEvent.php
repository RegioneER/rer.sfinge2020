<?php

namespace FascicoloBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Description of IstanzaFascicoloEvent
 *
 * @author aturdo
 */
class IstanzaFascicoloEvent extends Event
{
    protected $istanzaFascicolo;
	
	protected $response;

	public function __construct(\FascicoloBundle\Entity\IstanzaFascicolo $istanzaFascicolo)
    {
        $this->istanzaFascicolo = $istanzaFascicolo;
    }

    public function getIstanzaFascicolo()
    {
        return $this->istanzaFascicolo;
    }
	
	public function getResponse() {
		return $this->response;
	}

	public function setResponse($response) {
		$this->response = $response;
	}

}
