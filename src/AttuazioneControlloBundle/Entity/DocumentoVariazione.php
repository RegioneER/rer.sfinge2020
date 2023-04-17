<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;

/**
 * DocumentoVariazione
 *
 * @ORM\Table(name="documenti_variazione")
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\DocumentoVariazioneRepository")
 */
class DocumentoVariazione  extends EntityLoggabileCancellabile {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $documento_file;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\VariazioneRichiesta", inversedBy="documenti_variazione")
     * @ORM\JoinColumn(nullable=false)
     */
    private $variazione;

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
     * @return DocumentoVariazione
     */
    public function setDocumentoFile(\DocumentoBundle\Entity\DocumentoFile $documentoFile)
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
     * Set variazione
     *
     * @param \AttuazioneControlloBundle\Entity\VariazioneRichiesta $variazione
     * @return DocumentoVariazione
     */
    public function setVariazione(\AttuazioneControlloBundle\Entity\VariazioneRichiesta $variazione)
    {
        $this->variazione = $variazione;

        return $this;
    }

    /**
     * Get variazione
     *
     * @return \AttuazioneControlloBundle\Entity\VariazioneRichiesta 
     */
    public function getVariazione()
    {
        return $this->variazione;
    }
    
	public function getSoggetto() {
		return $this->getVariazione()->getSoggetto();
	}    
	
}
