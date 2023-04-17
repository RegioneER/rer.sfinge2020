<?php


namespace SoggettoBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaIncaricatiGestione extends RicercaIncaricati
{

    protected $denominazione;
    protected $codice_fiscale_soggetto;
    protected $stato_incarico;
	protected $is_manager_pa = false;

	/**
     * @return mixed
     */
    public function getStatoIncarico()
    {
        return $this->stato_incarico;
    }

    /**
     * @param mixed $stato_incarico
     */
    public function setStatoIncarico($stato_incarico)
    {
        $this->stato_incarico = $stato_incarico;
    }
	
	function getIsManagerPa() {
		return $this->is_manager_pa;
	}

	function setIsManagerPa($is_manager_pa) {
		$this->is_manager_pa = $is_manager_pa;
	}

    /**
     * @return mixed
     */
    public function getDenominazione()
    {
        return $this->denominazione;
    }

    /**
     * @param mixed $denominazione
     */
    public function setDenominazione($denominazione)
    {
        $this->denominazione = $denominazione;
    }

    /**
     * @return mixed
     */
    public function getCodiceFiscaleSoggetto()
    {
        return $this->codice_fiscale_soggetto;
    }

    /**
     * @param mixed $codice_fiscale_soggetto
     */
    public function setCodiceFiscaleSoggetto($codice_fiscale_soggetto)
    {
        $this->codice_fiscale_soggetto = $codice_fiscale_soggetto;
    }

    public function getType()
    {
		if($this->getIsManagerPa()){
			return "SoggettoBundle\Form\RicercaIncaricatiGestioneManagerPAType";
		}else{
			return "SoggettoBundle\Form\RicercaIncaricatiGestioneType";
		}
    }

    public function getNomeRepository()
    {
        return "SoggettoBundle:IncaricoPersona";
    }

    public function getNomeMetodoRepository()
    {
		if($this->getIsManagerPa()){
			return "getIncarichiPersoneGestioneManagerPA";
		}else{
			return "getIncarichiPersoneGestione";
		}
    }

    public function getNumeroElementiPerPagina()
    {
        return null;
    }

    public function getNomeParametroPagina()
    {
        return "page";
    }
	
	/*function mergeFreshData($freshData) {
		$this->setIncarico($freshData->getIncarico());
	}*/

}