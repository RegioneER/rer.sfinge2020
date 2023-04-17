<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\IngegneriaFinanziariaRepository")
 */
class IngegneriaFinanziaria extends Procedura{

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
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\TipoIngegneriaFinanziaria")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $tipo_ingegneria_finanziaria;  

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
     * @return mixed
     */
    public function getDocumentoConvenzione()
    {
        return $this->documento_convenzione;
    }

    /**
     * @param mixed $documento_convenzione
     */
    public function setDocumentoConvenzione($documento_convenzione)
    {
        $this->documento_convenzione = $documento_convenzione;
    }

    public function getTipo() {
        return "INGEGNERIA_FINANZIARIA";
    }
	
	public function getTipoIngegneriaFinanziaria() {
		return $this->tipo_ingegneria_finanziaria;
	}

	public function setTipoIngegneriaFinanziaria($tipo_ingegneria_finanziaria) {
		$this->tipo_ingegneria_finanziaria = $tipo_ingegneria_finanziaria;
	}

}