<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloRichiestaChiarimenti extends RichiestaProtocolloFinanziamento implements EmailSendableInterface {

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $richiesta_chiarimenti;

	function getRichiesta() {
		return $this->richiesta_chiarimenti->getPagamento()->getRichiesta();
	}

	public function getNomeClasse() {
		return "ProtocolloRichiestaChiarimenti";
	}

	public function getRichiestaChiarimento() {
		return $this->richiesta_chiarimenti;
	}

	public function setRichiestaChiarimento($richiesta_chiarimenti) {
		$this->richiesta_chiarimenti = $richiesta_chiarimenti;
	}

	public function getDestinatarioEmailProtocollo() {
		if ($this->getRichiesta()->getProcedura()->getId() == 7) {
			return $this->richiesta_chiarimenti->getPagamento()->getDurc()->getEmailPec();
		} else {
			return $this->richiesta_chiarimenti->getSoggetto()->getEmailPec();
		}
	}

	public function getTestoEmailProtocollo() {
		return $this->richiesta_chiarimenti->getTestoEmail();
	}

}
