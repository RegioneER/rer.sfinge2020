<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Table(name="documenti_proroghe")
 * @ORM\Entity()
 */
class DocumentoProroga extends EntityLoggabileCancellabile
{

    const CODICE_DOCUMENTO = 'DOCUMENTAZIONE_PROROGA';
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false, name="documento_id")
     */
    protected $documento;

    /**
     * @ORM\ManyToOne(targetEntity="Proroga",  inversedBy="documenti")
     * @ORM\JoinColumn(nullable=false, name="proroga_id")
     */
    protected $proroga;

    /**
     * @param Proroga|null $proroga
     */
    public function __construct(Proroga $proroga =null)
    {
       $this->proroga = $proroga;
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
     * Set documento
     *
     * @param \DocumentoBundle\Entity\DocumentoFile $documento
     * @return DocumentoProroga
     */
    public function setDocumento(\DocumentoBundle\Entity\DocumentoFile $documento)
    {
        $this->documento = $documento;

        return $this;
    }

    /**
     * Get documento
     *
     * @return \DocumentoBundle\Entity\DocumentoFile 
     */
    public function getDocumento()
    {
        return $this->documento;
    }

    /**
     * Set proroga
     *
     * @param \AttuazioneControlloBundle\Entity\Proroga $proroga
     * @return DocumentoProroga
     */
    public function setProroga(\AttuazioneControlloBundle\Entity\Proroga $proroga)
    {
        $this->proroga = $proroga;

        return $this;
    }

    /**
     * Get proroga
     *
     * @return \AttuazioneControlloBundle\Entity\Proroga 
     */
    public function getProroga()
    {
        return $this->proroga;
    }

    public function getSoggetto() {
        return $this->proroga->getRichiesta()->getSoggetto();
    }
}
