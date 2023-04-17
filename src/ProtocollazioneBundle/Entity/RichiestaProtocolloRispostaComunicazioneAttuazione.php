<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloRispostaComunicazioneAttuazione extends RichiestaProtocollo {
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\RispostaComunicazioneAttuazione", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $risposta_comunicazione_attuazione;
        
	public function getRispostaComunicazioneAttuazione() {
		return $this->risposta_comunicazione_attuazione;
	}

	public function setRispostaComunicazioneAttuazione($risposta_comunicazione_attuazione) {
		$this->risposta_comunicazione_attuazione = $risposta_comunicazione_attuazione;
	}
	
	public function getNomeClasse() {
		return "RichiestaProtocolloRispostaComunicazioneAttuazione";
	}

}
