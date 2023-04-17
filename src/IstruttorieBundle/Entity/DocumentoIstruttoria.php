<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use RichiesteBundle\Entity\Richiesta;
use DocumentoBundle\Entity\DocumentoFile;

/**
 * @ORM\Entity(repositoryClass="IstruttorieBundle\Repository\DocumentoIstruttoriaRepository")
 * @ORM\Table(name="documenti_istruttoria",
 *  indexes={
 *      @ORM\Index(name="idx_documento_ist_richiesta_file_id", columns={"documento_file_id"}),
 *      @ORM\Index(name="idx_documento_ist_richiesta_id", columns={"documento_richiesta_id"})
 *  })
 */
class DocumentoIstruttoria extends EntityLoggabileCancellabile {

   /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
     * @ORM\JoinColumn(name="documento_file_id", referencedColumnName="id")
     */
    private $documento_file;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="documenti_istruttoria")
     * @ORM\JoinColumn(name="documento_richiesta_id", referencedColumnName="id", nullable=false)
     */
    private $richiesta;

    public function __construct(?Richiesta $richiesta = null, ?DocumentoFile $documento = null){
        $this->richiesta = $richiesta;
        $this->documento_file = $documento;
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set documento_file
     *
     * @param \DocumentoBundle\Entity\DocumentoFile $documentoFile
     * @return DocumentoIstruttoria
     */
    public function setDocumentoFile(\DocumentoBundle\Entity\DocumentoFile $documentoFile = null)
    {
        $this->documento_file = $documentoFile;

        return $this;
    }

    /**
     * Get documento_file
     *
     * @return \DocumentoBundle\Entity\DocumentoFile 
     */
    public function getDocumentoFile()
    {
        return $this->documento_file;
    }

    /**
     * Set richiesta
     *
     * @param \RichiesteBundle\Entity\Richiesta $richiesta
     * @return DocumentoIstruttoria
     */
    public function setRichiesta(\RichiesteBundle\Entity\Richiesta $richiesta)
    {
        $this->richiesta = $richiesta;

        return $this;
    }

    /**
     * Get richiesta
     *
     * @return \RichiesteBundle\Entity\Richiesta 
     */
    public function getRichiesta()
    {
        return $this->richiesta;
    }
}
