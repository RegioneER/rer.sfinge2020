<?php

namespace SoggettoBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use DocumentoBundle\Entity\DocumentoFile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass="SoggettoBundle\Entity\DocumentoIncaricoRepository")
 * @ORM\Table(name="documenti_incarico")
 */
class DocumentoIncarico extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
     * @ORM\JoinColumn(name="documento_file_id", referencedColumnName="id")
     * @Assert\Valid
     */
    private $documentoFile;

    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\IncaricoPersona", inversedBy="documentiIncarico")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $incarico;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotNull
     */
    protected $nota;
    
    public function getId() {
        return $this->id;
    }

    public function getDocumentoFile() {
        return $this->documentoFile;
    }

    public function getIncarico() {
        return $this->incarico;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setDocumentoFile($documentoFile): void {
        $this->documentoFile = $documentoFile;
    }

    public function setIncarico($incarico): void {
        $this->incarico = $incarico;
    }

    public function getNota() {
        return $this->nota;
    }

    public function setNota($nota): void {
        $this->nota = $nota;
    }

}
