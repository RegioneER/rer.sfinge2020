<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloEsitoIstruttoria extends RichiestaProtocolloFinanziamento implements EmailSendableInterface {
	
	/**
	 * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoria", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $comunicazione_esito;
        
	public function getComunicazioneEsito() {
		return $this->comunicazione_esito;
	}

	public function setComunicazioneEsito($comunicazione_esito) {
		$this->comunicazione_esito = $comunicazione_esito;
	}
	
	public function getNomeClasse() {
		return "RichiestaProtocolloEsitoIstruttoria";
	}
	
	public function getDestinatarioEmailProtocollo(){
		return $this->comunicazione_esito->getIstruttoria()->getRichiesta()->getSoggetto()->getEmailPec();
	}
	
	public function getTestoEmailProtocollo() {
		return $this->comunicazione_esito->getTestoEmail();
	}
	
	public function getSoggetto() {
		return $this->comunicazione_esito->getSoggetto();
	}
		
}
