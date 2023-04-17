<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloIntegrazionePagamento extends RichiestaProtocolloFinanziamento implements EmailSendableInterface {

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $integrazione_pagamento;

	function getRichiesta() {
		return $this->integrazione_pagamento->getPagamento()->getRichiesta();
	}

	public function getNomeClasse() {
		return "ProtocolloIntegrazionePagamento";
	}

	public function getIntegrazionePagamento() {
		return $this->integrazione_pagamento;
	}

	public function setIntegrazionePagamento($integrazione_pagamento) {
		$this->integrazione_pagamento = $integrazione_pagamento;
	}

	public function getDestinatarioEmailProtocollo() {
		if ($this->getRichiesta()->getProcedura()->getId() == 7) {
			return $this->integrazione_pagamento->getPagamento()->getDurc()->getEmailPec();
		} else {
			return $this->integrazione_pagamento->getSoggetto()->getEmailPec();
		}
	}

	public function getTestoEmailProtocollo() {
		return $this->integrazione_pagamento->getTestoEmail();
	}

}
