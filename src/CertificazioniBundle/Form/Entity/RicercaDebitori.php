<?php

namespace CertificazioniBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaDebitori extends AttributiRicerca
{
	
	protected $cup;
	
	protected $asse;
	
	protected $beneficiario;

	protected $numeroElementiPerPagina=null;	

	public function getBeneficiario() {
		return $this->beneficiario;
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
	
	public function getAsse() {
		return $this->asse;
	}

	public function setAsse($asse) {
		$this->asse = $asse;
	}
	
	public function getNomeRepository()
    {
        return "CertificazioniBundle:RegistroDebitori";
    }
	
	public function getType()
    {
        return "CertificazioniBundle\Form\RicercaDebitoriType";
    }

    public function getNomeMetodoRepository()
    {
        return "ricercaProgettiIrregolariPerAsse";
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