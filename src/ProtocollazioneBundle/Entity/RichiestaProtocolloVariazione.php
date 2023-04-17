<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ProtocollazioneBundle\Repository\RichiestaProtocolloVariazioneRepository")
 */
class RichiestaProtocolloVariazione extends RichiestaProtocollo {
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\VariazioneRichiesta", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(name="variazione_id", referencedColumnName="id", nullable=true)
	 */
	protected $variazione;

	public function getVariazione() {
		return $this->variazione;
	}

	public function setVariazione($variazione) {
		$this->variazione = $variazione;
	}

	function getRichiesta() {
		$this->variazione->getRichiesta();
	}

	public function getNomeClasse() {
		return "ProtocolloVariazione";
	}

}
