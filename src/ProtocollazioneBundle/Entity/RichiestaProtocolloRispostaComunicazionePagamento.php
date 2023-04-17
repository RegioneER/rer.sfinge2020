<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloRispostaComunicazionePagamento extends RichiestaProtocolloFinanziamento {

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\RispostaComunicazionePagamento", inversedBy="richieste_protocollo")
     * @ORM\JoinColumn(nullable=true)
     */
    private $risposta_comunicazione_pagamento;

    function getRispostaComunicazionePagamento() {
        return $this->risposta_comunicazione_pagamento;
    }

    function setRispostaComunicazionePagamento($risposta_comunicazione_pagamento) {
        $this->risposta_comunicazione_pagamento = $risposta_comunicazione_pagamento;
    }

    public function getNomeClasse() {
        return "RichiestaProtocolloRispostaComunicazionePagamento";
    }
}
