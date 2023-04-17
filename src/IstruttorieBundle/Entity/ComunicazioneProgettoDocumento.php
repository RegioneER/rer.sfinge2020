<?php

namespace IstruttorieBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="comunicazioni_progetti_documenti")
 */
class ComunicazioneProgettoDocumento extends EntityLoggabileCancellabile
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

   /**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $documento_file;

    /**
     * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\ComunicazioneProgetto", inversedBy="documenti_comunicazione")
     * @ORM\JoinColumn(nullable=false)
     */
    private $comunicazione;
	
    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente")
     * @ORM\JoinColumn(nullable=true)
     */
    private $proponente;	
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $nota;
	
	/**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $descrizione;
        
	public function getId() {
		return $this->id;
	}

	public function getComunicazione() {
		return $this->comunicazione;
	}

	public function getProponente() {
		return $this->proponente;
	}

	public function getNota() {
		return $this->nota;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setComunicazione($comunicazione) {
		$this->comunicazione = $comunicazione;
	}

	public function setProponente($proponente) {
		$this->proponente = $proponente;
	}

	public function setNota($nota) {
		$this->nota = $nota;
	}

	public function getDocumentoFile() {
		return $this->documento_file;
	}

	public function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	public function getDescrizione() {
		return $this->descrizione;
	}

	public function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;
	}

}
