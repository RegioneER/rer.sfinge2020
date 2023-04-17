<?php


namespace RichiesteBundle\Form\Entity\AssistenzaTecnica;

use RichiesteBundle\Entity\Richiesta;

class AssistenzaTecnicaBeneficiario  {
	
	private $beneficiario;
	
	function getBeneficiario() {
		return $this->beneficiario;
	}

	function setBeneficiario($beneficiario) {
		$this->beneficiario = $beneficiario;
	}
	
}
