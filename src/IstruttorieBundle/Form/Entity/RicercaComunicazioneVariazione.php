<?php

namespace IstruttorieBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaComunicazioneVariazione extends AttributiRicerca
{
    private $procedura;
	
	private $protocollo;
	
	private $soggetto;

	function getProcedura() {
		return $this->procedura;
	}

	function getProtocollo() {
		return $this->protocollo;
	}

	function getSoggetto() {
		return $this->soggetto;
	}

	function setProcedura($procedura) {
		$this->procedura = $procedura;
	}

	function setProtocollo($protocollo) {
		$this->protocollo = $protocollo;
	}

	function setSoggetto($soggetto) {
		$this->soggetto = $soggetto;
	}

	public function getType()
    {
        return "IstruttorieBundle\Form\RicercaComunicazioneVariazioneType";
    }

    public function getNomeRepository()
    {
        return "IstruttorieBundle:ComunicazioneProgetto";
    }

    public function getNomeMetodoRepository()
    {
        return "getElencoComunicazioniVariazioni";
    }

    public function getNumeroElementiPerPagina()
    {
        return null;
    }

    public function getNomeParametroPagina()
    {
        return "page";
    }	

	function mergeFreshData($freshData) {
		$this->setSoggetto($freshData->getSoggetto());
	}	
	
}