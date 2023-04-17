<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloComunicazioneAttuazione extends RichiestaProtocollo implements EmailSendableInterface {
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\ComunicazioneAttuazione", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $comunicazione_attuazione;
        
	public function getComunicazioneAttuazione() {
		return $this->comunicazione_attuazione;
	}

	public function setComunicazioneAttuazione($comunicazione_attuazione) {
		$this->comunicazione_attuazione = $comunicazione_attuazione;
	}
	
	public function getNomeClasse() {
		return "RichiestaProtocolloComunicazioneAttuazione";
	}
	
	public function getDestinatarioEmailProtocollo(){
		return $this->comunicazione_attuazione->getRichiesta()->getSoggetto()->getEmailPec();
	}
	
	public function getTestoEmailProtocollo() {
		return $this->comunicazione_attuazione->getTestoEmail();
	}
	
	public function getSoggetto() {
		return $this->comunicazione_attuazione->getSoggetto();
	}
		
}
