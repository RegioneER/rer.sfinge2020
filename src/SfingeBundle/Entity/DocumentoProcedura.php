<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use DocumentoBundle\Entity\DocumentoFile;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documenti_procedure",
 * indexes={
 *      @ORM\Index(name="idx_documento_id", columns={"documento_id"}),
 *      @ORM\Index(name="idx_procedura_id", columns={"procedura_id"}),
 *  })
 *
 */
class DocumentoProcedura extends EntityLoggabileCancellabile{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="descrizione")
     *
     */
    private $descrizione;


    /**
     * @ORM\ManyToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
     * @ORM\JoinColumn(name="documento_id", referencedColumnName="id", nullable=false)
     * @Assert\Valid
     * 
     */
    protected $documento;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="documenti")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=true)
     *
     */
    protected $procedura;

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
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDescrizione()
    {
        return $this->descrizione;
    }

    /**
     * @param mixed $descrizione
     */
    public function setDescrizione($descrizione)
    {
        $this->descrizione = $descrizione;
    }

    /**
     * @return mixed
     */
    public function getDocumento()
    {
        return $this->documento;
    }

    /**
     * @param mixed $documento
     */
    public function setDocumento($documento)
    {
        $this->documento = $documento;
    }

    /**
     * @return mixed
     */
    public function getProcedura()
    {
        return $this->procedura;
    }

    /**
     * @param mixed $procedura
     */
    public function setProcedura($procedura)
    {
        $this->procedura = $procedura;
    }

}
