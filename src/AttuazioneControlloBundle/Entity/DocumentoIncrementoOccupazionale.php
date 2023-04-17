<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documenti_incremento_occupazionale")
 *  })
 */
class DocumentoIncrementoOccupazionale extends EntityLoggabileCancellabile
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="documento_file_id", referencedColumnName="id")
     * @Assert\Valid
     * 
     */
    public $documento_file;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\IncrementoOccupazionale", inversedBy="documenti_incremento_occupazionale")
     * @ORM\JoinColumn(nullable=false)
     */
    public $incremento_occupazionale;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDocumentoFile()
    {
        return $this->documento_file;
    }

    /**
     * @param mixed $documento_file
     */
    public function setDocumentoFile($documento_file): void
    {
        $this->documento_file = $documento_file;
    }

    /**
     * @return mixed
     */
    public function getIncrementoOccupazionale()
    {
        return $this->incremento_occupazionale;
    }

    /**
     * @param mixed $incremento_occupazionale
     */
    public function setIncrementoOccupazionale($incremento_occupazionale): void
    {
        $this->incremento_occupazionale = $incremento_occupazionale;
    }   
}
