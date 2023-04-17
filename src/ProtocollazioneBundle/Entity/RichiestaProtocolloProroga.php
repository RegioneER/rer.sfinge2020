<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloProroga extends RichiestaProtocollo {
    
 	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Proroga", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $proroga;
    
    function getProroga() {
        return $this->proroga;
    }

    function setProroga($proroga) {
        $this->proroga = $proroga;
    }

	public function getNomeClasse() {
		return "ProtocolloProroga";
	}

}
