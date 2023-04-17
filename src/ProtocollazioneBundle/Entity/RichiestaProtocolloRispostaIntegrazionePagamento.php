<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloRispostaIntegrazionePagamento extends RichiestaProtocolloFinanziamento {
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\RispostaIntegrazionePagamento", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $risposta_integrazione_pagamento;
        
	function getRispostaIntegrazionePagamento() {
		return $this->risposta_integrazione_pagamento;
	}

	function setRispostaIntegrazionePagamento($risposta_integrazione_pagamento) {
		$this->risposta_integrazione_pagamento = $risposta_integrazione_pagamento;
	}
	
	public function getNomeClasse() {
		return "RichiestaProtocolloRispostaIntegrazionePagamento";
	}

}
