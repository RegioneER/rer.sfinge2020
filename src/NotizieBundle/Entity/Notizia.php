<?php

namespace NotizieBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Notizia
 *
 * @ORM\Table(name="notizie")
 * @ORM\Entity(repositoryClass="NotizieBundle\Entity\NotiziaRepository")
 */
class Notizia extends EntityLoggabileCancellabile
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
     * @var string
     *
	 * @Assert\NotNull
     * @ORM\Column(name="titolo", type="string", length=1024, nullable=false)
     */
    private $titolo;

    /**
     * @var string
     *
     * @ORM\Column(name="testo", type="text", nullable=true)
     */
    private $testo;

    /**
     * @var string
     *
     * @ORM\Column(name="documento_nome", type="string", length=255, nullable=true)
     */
    private $documentoNome;

    /**
     * @var string
     *
     * @ORM\Column(name="documento_path", type="string", length=1024, nullable=true)
     */
    private $documentoPath;

    /**
     * @var \DateTime
     *
	 * @Assert\NotNull
     * @ORM\Column(name="data_inserimento", type="datetime",nullable=false)
     */
    private $dataInserimento;

    /**
     * @var \DateTime
     *
	 * @Assert\NotNull
     * @ORM\Column(name="data_inizio_visualizzazione", type="datetime",nullable=false)
     */
    private $dataInizioVisualizzazione;
	
	/**
     * @var \DateTime
     *
	 * @Assert\NotNull
     * @ORM\Column(name="data_fine_visualizzazione", type="datetime",nullable=false)
     */
    private $dataFineVisualizzazione;
	
	/**
	 *
	 * @var array 
	 * 
	 * @Assert\NotBlank()
	 * @ORM\Column(name="visibilita", type="array", nullable=false)
	 */
	private $visibilita;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="richieste")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=true)
     */
    private $procedura;


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
     * Set titolo
     *
     * @param string $titolo
     *
     * @return Notizia
     */
    public function setTitolo($titolo)
    {
        $this->titolo = $titolo;

        return $this;
    }

    /**
     * Get titolo
     *
     * @return string
     */
    public function getTitolo()
    {
        return $this->titolo;
    }

    /**
     * Set testo
     *
     * @param string $testo
     *
     * @return Notizia
     */
    public function setTesto($testo)
    {
        $this->testo = $testo;

        return $this;
    }

    /**
     * Get testo
     *
     * @return string
     */
    public function getTesto()
    {
        return $this->testo;
    }

    /**
     * Set documentoNome
     *
     * @param string $documentoNome
     *
     * @return Notizia
     */
    public function setDocumentoNome($documentoNome)
    {
        $this->documentoNome = $documentoNome;

        return $this;
    }

    /**
     * Get documentoNome
     *
     * @return string
     */
    public function getDocumentoNome()
    {
        return $this->documentoNome;
    }

    /**
     * Set documentoPath
     *
     * @param string $documentoPath
     *
     * @return Notizia
     */
    public function setDocumentoPath($documentoPath)
    {
        $this->documentoPath = $documentoPath;

        return $this;
    }

    /**
     * Get documentoPath
     *
     * @return string
     */
    public function getDocumentoPath()
    {
        return $this->documentoPath;
    }

    /**
     * Set dataInserimento
     *
     * param \DateTime $dataInserimento
     *
     * @return Notizia
     */
    public function setDataInserimento($dataInserimento)
    {
        $this->dataInserimento = $dataInserimento;

        return $this;
    }

    /**
     * Get dataInserimento
     *
     * @return \DateTime
     */
    public function getDataInserimento()
    {
        return $this->dataInserimento;
    }

    /**
     * Set dataInizioVisualizzazione
     *
     * param \DateTime $dataInizioVisualizzazione
     *
     * @return Notizia
     */
    public function setDataInizioVisualizzazione($dataInizioVisualizzazione)
    {
        $this->dataInizioVisualizzazione = $dataInizioVisualizzazione;

        return $this;
    }

    /**
     * Get dataInizioVisualizzazione
     *
     * @return \DateTime
     */
    public function getDataInizioVisualizzazione()
    {
        return $this->dataInizioVisualizzazione;
    }
	
	function getDataFineVisualizzazione() {
		return $this->dataFineVisualizzazione;
	}

	function setDataFineVisualizzazione($dataFineVisualizzazione) {
		$this->dataFineVisualizzazione = $dataFineVisualizzazione;
	}
	
	
	function getVisibilita() {
		return $this->visibilita;
	}

	function setVisibilita($visibilita) {
		$this->visibilita = $visibilita;
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

