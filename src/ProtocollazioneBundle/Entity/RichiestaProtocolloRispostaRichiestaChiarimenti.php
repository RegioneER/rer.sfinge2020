<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloRispostaRichiestaChiarimenti extends RichiestaProtocolloFinanziamento {
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $risposta_richiesta_chiarimenti;
        
	function getRispostaRichiestaChiarimenti() {
		return $this->risposta_richiesta_chiarimenti;
	}

	function setRispostaRichiestaChiarimenti($risposta_richiesta_chiarimenti) {
		$this->risposta_richiesta_chiarimenti = $risposta_richiesta_chiarimenti;
	}
	
	public function getNomeClasse() {
		return "RichiestaProtocolloRispostaRichiestaChiarimenti";
	}

}
