<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use DocumentoBundle\Entity\DocumentoFile;
use Symfony\Component\Validator\Constraints AS Assert;
use RichiesteBundle\Entity\Richiesta;

/**
 * @ORM\Entity
 * @ORM\Table(name="documenti_impegni")
 */
class DocumentoImpegno extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="RichiestaImpegni", inversedBy="documenti")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     * @var RichiestaImpegni
     */
    protected $impegno;
    /**
     * @ORM\ManyToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     * @var DocumentoFile
     */
    protected $documento;

    public function __construct(?RichiestaImpegni $impegno = null, ?DocumentoFile $documento = null) {
        $this->impegno = $impegno;
        $this->documento = $documento;
    }

    public function getId():?int {
        return $this->id;
    }

    public function setImpegno(RichiestaImpegni $impegno): self {
        $this->impegno = $impegno;

        return $this;
    }

    public function getImpegno(): ?RichiestaImpegni {
        return $this->impegno;
    }

    public function setDocumento(DocumentoFile $documento): self {
        $this->documento = $documento;

        return $this;
    }

    public function getDocumento(): DocumentoFile {
        return $this->documento;
    }

    public function getRichiesta():Richiesta{
        return $this->impegno->getRichiesta();
    }
}
