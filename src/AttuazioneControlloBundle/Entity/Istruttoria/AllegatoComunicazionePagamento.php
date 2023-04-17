<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use BaseBundle\Entity\Id;
use DocumentoBundle\Entity\DocumentoFile;

/**
 * @ORM\Entity()
 * @ORM\Table(name="allegati_comunicazioni_pagamento")
 */
class AllegatoComunicazionePagamento extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @var ComunicazionePagamento
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento", inversedBy="allegati")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $comunicazione_pagamento;

    /**
     * @var DocumentoFile
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $documento;

    
    public function __construct(?ComunicazionePagamento $comunicazionePagamento = null, ?DocumentoFile $documento = null) {
        $this->comunicazione_pagamento = $comunicazionePagamento;
        $this->documento = $documento;
    }

    public function getDocumento(): ?DocumentoFile {
        return $this->documento;
    }

    public function setDocumento(DocumentoFile $documento): self {
        $this->documento = $documento;

        return $this;
    }

    public function getComunicazionePagamento(): ?ComunicazionePagamento {
        return $this->comunicazione_pagamento;
    }

    public function setComunicazionePagamento(ComunicazionePagamento $comunicazionePagamento): self {
        $this->comunicazione_pagamento = $comunicazionePagamento;

        return $this;
    }
}
