<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloEsitoIstruttoriaPagamento extends RichiestaProtocolloFinanziamento implements EmailSendableInterface {

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\EsitoIstruttoriaPagamento", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $esito_istruttoria_pagamento;

	function getRichiesta() {
		return $this->esito_istruttoria_pagamento->getPagamento()->getRichiesta();
	}

	public function getNomeClasse() {
		return "ProtocolloEsitoIstruttoriaPagamento";
	}

	public function getEsitoIstruttoriaPagamento() {
		return $this->esito_istruttoria_pagamento;
	}

	public function setEsitoIstruttoriaPagamento($esito_istruttoria_pagamento) {
		$this->esito_istruttoria_pagamento = $esito_istruttoria_pagamento;
	}
	
	public function getDestinatarioEmailProtocollo() {
		if ($this->getRichiesta()->getProcedura()->getId() == 7) {
			return $this->esito_istruttoria_pagamento->getPagamento()->getDurc()->getEmailPec();
		} else {
			return $this->esito_istruttoria_pagamento->getSoggetto()->getEmailPec();
		}
	}

	public function getTestoEmailProtocollo() {
		return $this->esito_istruttoria_pagamento->getTestoEmail();
	}
	
	public function getSoggetto() {
		return $this->esito_istruttoria_pagamento->getSoggetto();
	}

}
