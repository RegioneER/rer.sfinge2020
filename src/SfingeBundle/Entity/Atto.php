<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use DocumentoBundle\Entity\DocumentoFile;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\AttoRepository")
 * @ORM\Table(name="atti",
 * indexes={
 *      @ORM\Index(name="idx_tipo_atto_id", columns={"tipo_atto_id"}),
 *      @ORM\Index(name="idx_dirigente_responsabile_id", columns={"dirigente_responsabile_id"}),
 *  })
 */
class Atto extends EntityLoggabileCancellabile{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\TipoAtto")
     * @ORM\JoinColumn(name="tipo_atto_id",referencedColumnName="id", nullable=true)
     *
     * @Assert\NotBlank()
     */
    protected $tipo_atto;

    /**
     * @ORM\Column(type="string", length=32, nullable=true, name="numero")
     *
     * @Assert\NotBlank()
     */
    private $numero;

    /**
     * @ORM\Column(type="string", length=512, nullable=true, name="titolo")
     */
    private $titolo;

    /**
     * @ORM\ManyToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
     * @ORM\JoinColumn(name="documento_id", referencedColumnName="id", nullable=true)
     * @Assert\Valid
     * 
     */
    protected $documento_atto;

    /**
     * @ORM\ManyToOne(targetEntity="AnagraficheBundle\Entity\Persona")
     * @ORM\JoinColumn(name="dirigente_responsabile_id", referencedColumnName="id", nullable=true)
     */
    private $dirigente_responsabile;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $data_pubblicazione;
	
    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="atti")
     * @ORM\JoinColumn(nullable=true)
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
    public function getTipoAtto()
    {
        return $this->tipo_atto;
    }

    /**
     * @param mixed $tipo_atto
     */
    public function setTipoAtto($tipo_atto)
    {
        $this->tipo_atto = $tipo_atto;
    }

    /**
     * @return mixed
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @param mixed $numero
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
    }

    /**
     * @return mixed
     */
    public function getTitolo()
    {
        return $this->titolo;
    }

    /**
     * @param mixed $titolo
     */
    public function setTitolo($titolo)
    {
        $this->titolo = $titolo;
    }

    /**
     * @return DocumentoFile
     */
    public function getDocumentoAtto()
    {
        return $this->documento_atto;
    }

    /**
     * @param mixed $documento_atto
     */
    public function setDocumentoAtto($documento_atto)
    {
        $this->documento_atto = $documento_atto;
    }

    public function __toString(){
        return $this->getTipoAtto()->getDescrizione() . ' - ' . $this->getNumero() . ' - ' . $this->getTitolo();
    }


    /**
     * Set dataPubblicazione
     *
     * @param \DateTime $dataPubblicazione
     *
     * @return Atto
     */
    public function setDataPubblicazione($dataPubblicazione)
    {
        $this->data_pubblicazione = $dataPubblicazione;

        return $this;
    }

    /**
     * Get dataPubblicazione
     *
     * @return \DateTime
     */
    public function getDataPubblicazione()
    {
        return $this->data_pubblicazione;
    }

    /**
     * Set dirigenteResponsabile
     *
     * @param \AnagraficheBundle\Entity\Persona $dirigenteResponsabile
     *
     * @return Atto
     */
    public function setDirigenteResponsabile(\AnagraficheBundle\Entity\Persona $dirigenteResponsabile = null)
    {
        $this->dirigente_responsabile = $dirigenteResponsabile;

        return $this;
    }

    /**
     * Get dirigenteResponsabile
     *
     * @return \AnagraficheBundle\Entity\Persona
     */
    public function getDirigenteResponsabile()
    {
        return $this->dirigente_responsabile;
    }
	
	function getProcedura() {
		return $this->procedura;
	}

	function setProcedura($procedura) {
		$this->procedura = $procedura;
	}
	
	public function getTipoAttoString() {
		$res = "";
		switch ($this->tipo_atto->getCodice()) {
			case 'DELIBERA':
				$res = 'DGR';
				break;
			case 'DETERMINA':
				$res = 'DET';
				break;
			case 'DECRETO':
				$res = 'DEC';
				break;
		}
		return $res;
	}

}
