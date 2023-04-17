<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloComunicazioneProgetto extends RichiestaProtocolloFinanziamento implements EmailSendableInterface {
	
	/**
	 * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\ComunicazioneProgetto", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $comunicazione_progetto;
        
	public function getComunicazioneProgetto() {
		return $this->comunicazione_progetto;
	}

	public function setComunicazioneProgetto($comunicazione_progetto) {
		$this->comunicazione_progetto = $comunicazione_progetto;
	}
	
	public function getNomeClasse() {
		return "RichiestaProtocolloComunicazioneProgetto";
	}
	
	public function getDestinatarioEmailProtocollo(){
		return $this->comunicazione_progetto->getRichiesta()->getSoggetto()->getEmailPec();
	}
	
	public function getTestoEmailProtocollo() {
		return $this->comunicazione_progetto->getTestoEmail();
	}
	
	public function getSoggetto() {
		return $this->comunicazione_progetto->getSoggetto();
	}
		
}
