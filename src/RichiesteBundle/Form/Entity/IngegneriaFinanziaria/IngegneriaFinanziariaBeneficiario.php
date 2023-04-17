<?php


namespace RichiesteBundle\Form\Entity\IngegneriaFinanziaria;

use RichiesteBundle\Entity\Richiesta;

class IngegneriaFinanziariaBeneficiario {
	
	private $beneficiario;
	
	function getBeneficiario() {
		return $this->beneficiario;
	}

	function setBeneficiario($beneficiario) {
		$this->beneficiario = $beneficiario;
	}
	
}
