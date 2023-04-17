<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ProtocollazioneBundle\Repository\RichiestaProtocolloFinanziamentoRepository")
 */
class RichiestaProtocolloFinanziamento extends RichiestaProtocollo {

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=true)
	 */
	protected $richiesta;

	function getRichiesta() {
		return $this->richiesta;
	}

	function setRichiesta($richiesta) {
		$this->richiesta = $richiesta;
	}
	
	public function getNomeClasse() {
		return "ProtocolloFinanziamento";
	}

}
