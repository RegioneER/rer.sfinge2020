<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\AssistenzaTecnicaRepository")
 */
class AssistenzaTecnica extends Procedura{

    /**
     * @var \DateTime $data_convenzione
     *
     * @ORM\Column(name="data_convenzione", type="date", nullable=true)
     */
    protected $data_convenzione;

    /**
     *
     * @var \DateTime $data_programma_attivita
     *
     * @ORM\Column(name="data_programma_attivita", type="date", nullable=true)
     */
    protected $data_programma_attivita;

    /**
     * @ORM\ManyToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
     * @ORM\JoinColumn(name="documento_convenzione_id", referencedColumnName="id", nullable=true)
     *
     * @Assert\Valid()
     */
    protected $documento_convenzione;
    
    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\TipoAssistenzaTecnica")
     * @ORM\JoinColumn(nullable=true)
     *
     * @Assert\NotNull()
     */
    protected $tipo_assistenza_tecnica;    

    /**
     * @return \DateTime
     */
    public function getDataConvenzione()
    {
        return $this->data_convenzione;
    }

    /**
     * @param \DateTime $data_convenzione
     */
    public function setDataConvenzione($data_convenzione)
    {
        $this->data_convenzione = $data_convenzione;
    }

    /**
     * @return \DateTime
     */
    public function getDataProgrammaAttivita()
    {
        return $this->data_programma_attivita;
    }

    /**
     * @param \DateTime $data_programma_attivita
     */
    public function setDataProgrammaAttivita($data_programma_attivita)
    {
        $this->data_programma_attivita = $data_programma_attivita;
    }

    /**
     * @return \DocumentoBundle\Entity\DocumentoFile
     */
    public function getDocumentoConvenzione()
    {
        return $this->documento_convenzione;
    }

    /**
     * @param \DocumentoBundle\Entity\DocumentoFile $documento_convenzione
     */
    public function setDocumentoConvenzione($documento_convenzione)
    {
        $this->documento_convenzione = $documento_convenzione;
    }

    public function getTipoAssistenzaTecnica() {
        return $this->tipo_assistenza_tecnica;
    }

    public function setTipoAssistenzaTecnica($tipo_assistenza_tecnica) {
        $this->tipo_assistenza_tecnica = $tipo_assistenza_tecnica;
        return $this;
    }

    public function getTipo() {
        return "ASSISTENZA_TECNICA";
    }

    
}