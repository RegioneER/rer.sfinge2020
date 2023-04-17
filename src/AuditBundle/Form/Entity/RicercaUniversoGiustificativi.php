<?php

namespace AuditBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaUniversoGiustificativi extends AttributiRicerca
{	
	protected $richiesta;
    
    protected $certificazione;

    protected $numeroElementiPerPagina=null;
		
    public function getRichiesta() {
        return $this->richiesta;
    }

    public function getCertificazione() {
        return $this->certificazione;
    }

    public function setRichiesta($richiesta) {
        $this->richiesta = $richiesta;
    }

    public function setCertificazione($certificazione) {
        $this->certificazione = $certificazione;
    }
  
    public function getType()
    {
        return "AuditBundle\Form\RicercaUniversoGiustificativiType";
    }

    public function getNomeRepository()
    {
        return "AttuazioneControlloBundle:GiustificativoPagamento";
    }

    public function getNomeMetodoRepository()
    {
        return "getGiustificativiInUniverso";
    }

    public function getNumeroElementiPerPagina()
    {
        return $this->numeroElementiPerPagina;
    }

    public function getNomeParametroPagina()
    {
        return "page";
    }	

}