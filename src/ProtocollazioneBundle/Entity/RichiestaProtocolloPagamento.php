<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloPagamento extends RichiestaProtocollo {
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="richieste_protocollo")
	 * @ORM\JoinColumn(name="pagamento_id", referencedColumnName="id", nullable=true)
	 */
	protected $pagamento;
        
        /**
	 * @ORM\OneToOne(targetEntity="RichiestaProtocolloPagamento")
	 * @ORM\JoinColumn(name="richiesta_protocollo_pagamento_id", referencedColumnName="id", nullable=true)
	 */
	protected $richiesta_protocollo_pagamento_precedente;
        
        /**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $suddivisione_in_lotti;	

	function getRichiesta() {
		return $this->pagamento->getRichiesta();
	}

	public function getPagamento() {
		return $this->pagamento;
	}

	public function setPagamento($pagamento) {
		$this->pagamento = $pagamento;
	}

	public function getNomeClasse() {
		return "ProtocolloPagamento";
	}
        
        function getRichiestaProtocolloPagamentoPrecedente() {
            return $this->richiesta_protocollo_pagamento_precedente;
        }

        function getSuddivisioneInLotti() {
            return $this->suddivisione_in_lotti;
        }

        function setRichiestaProtocolloPagamentoPrecedente($richiesta_protocollo_pagamento_precedente): void {
            $this->richiesta_protocollo_pagamento_precedente = $richiesta_protocollo_pagamento_precedente;
        }

        function setSuddivisioneInLotti($suddivisione_in_lotti): void {
            $this->suddivisione_in_lotti = $suddivisione_in_lotti;
        }


}
