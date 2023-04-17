<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use BaseBundle\Entity\Id;
use DocumentoBundle\Entity\DocumentoFile;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimentoRepository")
 * @ORM\Table(name="allegati_richieste_chiarimenti")
 */
class AllegatoRichiestaChiarimento extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @var RichiestaChiarimento
     * @ORM\ManyToOne(targetEntity="RichiestaChiarimento", inversedBy="allegati")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $richiesta_chiarimento;

    /**
     * @var DocumentoFile
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $documento;

    public function __construct(?RichiestaChiarimento $richiestaChiarimento = null, ?DocumentoFile $documento = null) {
        $this->richiesta_chiarimento = $richiestaChiarimento;
        $this->documento = $documento;
    }

    public function getDocumento(): ?DocumentoFile {
        return $this->documento;
    }

    public function setDocumento(DocumentoFile $documento): self {
        $this->documento = $documento;

        return $this;
    }

    public function getRichiestaChiarimento(): ?RichiestaChiarimento {
        return $this->richiesta_chiarimento;
    }

    public function setRichiestaChiarimento(RichiestaChiarimento $richiesta_chiarimento): self {
        $this->richiesta_chiarimento = $richiesta_chiarimento;

        return $this;
    }
}
