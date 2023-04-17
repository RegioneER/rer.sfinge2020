<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloRispostaEsitoIstruttoria extends RichiestaProtocolloFinanziamento {
	
	/**
	 * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\RispostaComunicazioneEsitoIstruttoria", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $risposta_comunicazione;
        
	public function getRispostaComunicazione() {
		return $this->risposta_comunicazione;
	}

	public function setRispostaComunicazione($risposta_comunicazione) {
		$this->risposta_comunicazione = $risposta_comunicazione;
	}
	
	public function getNomeClasse() {
		return "RichiestaProtocolloRispostaEsitoIstruttoria";
	}

}
