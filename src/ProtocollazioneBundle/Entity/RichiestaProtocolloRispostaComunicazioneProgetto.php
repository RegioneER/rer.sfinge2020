<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloRispostaComunicazioneProgetto extends RichiestaProtocolloFinanziamento {
	
	/**
	 * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\RispostaComunicazioneProgetto", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $risposta_comunicazione_progetto;
        
	public function getRispostaComunicazioneProgetto() {
		return $this->risposta_comunicazione_progetto;
	}

	public function setRispostaComunicazioneProgetto($risposta_comunicazione_progetto) {
		$this->risposta_comunicazione_progetto = $risposta_comunicazione_progetto;
	}
	
	public function getNomeClasse() {
		return "RichiestaProtocolloRispostaComunicazioneProgetto";
	}

}
