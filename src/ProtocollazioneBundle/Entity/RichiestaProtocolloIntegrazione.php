<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloIntegrazione extends RichiestaProtocolloFinanziamento implements EmailSendableInterface{
	
	/**
	 * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\IntegrazioneIstruttoria", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $integrazione;
        
	function getIntegrazione() {
		return $this->integrazione;
	}

	function setIntegrazione($integrazione) {
		$this->integrazione = $integrazione;
	}
	
	public function getNomeClasse() {
		return "RichiestaProtocolloIntegrazione";
	}
	
	public function getDestinatarioEmailProtocollo(){
		return $this->integrazione->getSoggetto()->getEmailPec();
	}
	
	public function getTestoEmailProtocollo() {
		return $this->integrazione->getTestoEmail();
	}
		
}
