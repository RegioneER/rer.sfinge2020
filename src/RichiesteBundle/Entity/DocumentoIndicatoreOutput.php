<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DocumentoBundle\Entity\DocumentoFile;
use RichiesteBundle\Entity\IndicatoreOutput;

/**
 * @ORM\Entity
 * @ORM\Table(name="documenti_indicatori_output")
 */
class DocumentoIndicatoreOutput extends EntityLoggabileCancellabile
{
     /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="IndicatoreOutput", inversedBy="documenti")
	 * @ORM\JoinColumn(nullable=false)
     * 
     * @var IndicatoreOutput
     */
    protected $indicatore;
    /**
     * @ORM\ManyToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
	 * @ORM\JoinColumn(nullable=false)
     * @var DocumentoFile;
     */    
    protected $documento;

    public function __construct(IndicatoreOutput $indicatore, ?DocumentoFile $documento = null){
        $this->indicatore = $indicatore;
        $this->documento = $documento;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setIndicatore(IndicatoreOutput $indicatore): self
    {
        $this->indicatore = $indicatore;

        return $this;
    }

    public function getIndicatore(): IndicatoreOutput
    {
        return $this->indicatore;
    }

    public function setDocumento(DocumentoFile $documento = null): self
    {
        $this->documento = $documento;

        return $this;
    }

    public function getDocumento(): DocumentoFile
    {
        return $this->documento;
    }
}
