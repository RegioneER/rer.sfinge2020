<?php

namespace CertificazioniBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

abstract class RicercaPagamenti extends AttributiRicerca
{

    protected $procedura;
	
	protected $asse;
	
	protected $cup;
	
	protected $id_pagamento;
	
	protected $beneficiario;

	protected $numeroElementiPerPagina=null;	
	
    function getProcedura() {
        return $this->procedura;
    }

    function getAsse() {
        return $this->asse;
    }

    function setProcedura($procedura) {
        $this->procedura = $procedura;
        return $this;
    }

    function setAsse($asse) {
        $this->asse = $asse;
        return $this;
    }
	
	public function getIdPagamento() {
		return $this->id_pagamento;
	}

	public function getBeneficiario() {
		return $this->beneficiario;
	}

	public function setIdPagamento($id_pagamento) {
		$this->id_pagamento = $id_pagamento;
	}

	public function setBeneficiario($beneficiario) {
		$this->beneficiario = $beneficiario;
	}
	
	public function getCup() {
		return $this->cup;
	}

	public function setCup($cup) {
		$this->cup = $cup;
	}

	function setNumeroElementiPerPagina($numeroElementiPerPagina) {
		$this->numeroElementiPerPagina = $numeroElementiPerPagina;
	}
	
	public function getNomeRepository()
    {
        return "AttuazioneControlloBundle:Pagamento";
    }

    public function getNumeroElementiPerPagina()
    {
        return $this->numeroElementiPerPagina;
    }

    public function getNomeParametroPagina()
    {
        return "page";
    }
    
    public function mostraNumeroElementi()
    {
        return false;
    }    

}