<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloRispostaIntegrazione extends RichiestaProtocolloFinanziamento {
	
	/**
	 * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $risposta_integrazione;
        
	function getRispostaIntegrazione() {
		return $this->risposta_integrazione;
	}

	function setRispostaIntegrazione($risposta_integrazione) {
		$this->risposta_integrazione = $risposta_integrazione;
	}
	
	public function getNomeClasse() {
		return "RichiestaProtocolloRispostaIntegrazione";
	}

}
