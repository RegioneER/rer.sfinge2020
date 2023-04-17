<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Manuale
 *
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\ManualeRepository")
 * @ORM\Table(name="manuali",
 *  indexes={
 *      @ORM\Index(name="idx_documento_file_id", columns={"documento_file_id"})
 *  })
 */
class Manuale extends EntityLoggabileCancellabile 
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
     * @ORM\JoinColumn(name="documento_file_id", referencedColumnName="id")
     */
    private $documento_file;

    /**
     * @ORM\Column(type="string", length=1000,  name="descrizione", nullable=true)
     * @Assert\NotBlank()
     */
    protected $descrizione;
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set documentoFile
     *
     * @param \DocumentoBundle\Entity\DocumentoFile $documentoFile
     *
     * @return Manuale
     */
    public function setDocumentoFile(\DocumentoBundle\Entity\DocumentoFile $documentoFile = null)
    {
        $this->documento_file = $documentoFile;

        return $this;
    }

    /**
     * Get documentoFile
     *
     * @return \DocumentoBundle\Entity\DocumentoFile
     */
    public function getDocumentoFile()
    {
        return $this->documento_file;
    }
    
    function getDescrizione() {
        return $this->descrizione;
    }

    function setDescrizione($descrizione) {
        $this->descrizione = $descrizione;
    }

}
