<?php

namespace CertificazioniBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaPagamentiCertificati extends RicercaPagamenti {

	protected $certificazione;

	protected $id_operazione;
			
	function getCertificazione() {
		return $this->certificazione;
	}

	function setCertificazione($certificazione) {
		$this->certificazione = $certificazione;
	}
	
	function getIdOperazione() {
		return $this->id_operazione;
	}

	function setIdOperazione($id_operazione) {
		$this->id_operazione = $id_operazione;
	}	
	
	public function getType()
    {
        return "CertificazioniBundle\Form\RicercaPagamentiCertificatiType";
    }

    public function getNomeMetodoRepository()
    {
        return "getPagamentiCertificati";
    }

}
