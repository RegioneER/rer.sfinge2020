<?php


namespace RichiesteBundle\Form\Entity\Acquisizioni;

use RichiesteBundle\Entity\Richiesta;

class Beneficiario  {
	
	private $beneficiario;
	
	function getBeneficiario() {
		return $this->beneficiario;
	}

	function setBeneficiario($beneficiario) {
		$this->beneficiario = $beneficiario;
	}
	
}
