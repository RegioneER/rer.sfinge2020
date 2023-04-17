<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RichiestaProtocolloComunicazionePagamento extends RichiestaProtocolloFinanziamento implements EmailSendableInterface {

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento", inversedBy="richieste_protocollo")
     * @ORM\JoinColumn(nullable=true)
     */
    private $comunicazione_pagamento;

    function getRichiesta() {
        return $this->comunicazione_pagamento->getPagamento()->getRichiesta();
    }

    public function getNomeClasse() {
        return "ProtocolloComunicazionePagamento";
    }

    public function getComunicazionePagamento() {
        return $this->comunicazione_pagamento;
    }

    public function setComunicazionePagamento($comunicazione_pagamento) {
        $this->comunicazione_pagamento = $comunicazione_pagamento;
    }

    public function getDestinatarioEmailProtocollo() {
        if ($this->getRichiesta()->getProcedura()->getId() == 7) {
            return $this->comunicazione_pagamento->getPagamento()->getDurc()->getEmailPec();
        } else {
            return $this->comunicazione_pagamento->getSoggetto()->getEmailPec();
        }
    }

    public function getTestoEmailProtocollo() {
        return $this->comunicazione_pagamento->getTestoEmail();
    }
}
