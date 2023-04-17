<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\DocumentoProponenteRepository")
 * @ORM\Table(name="documenti_proponente",
 *  indexes={
 *      @ORM\Index(name="idx_documento_proponente_file_id", columns={"documento_file_id"}),
 *		@ORM\Index(name="idx_documento_proponente_id", columns={"documento_proponente_id"})
 *  })
 */
class DocumentoProponente extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
	 * @ORM\JoinColumn(name="documento_file_id", referencedColumnName="id")
	 */
	private $documento_file;
	
	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente", inversedBy="documenti_proponente")
	 * @ORM\JoinColumn(name="documento_proponente_id", referencedColumnName="id", nullable=false)
	 */
	private $proponente;

	public function getId() {
		return $this->id;
	}

	public function getDocumentoFile() {
		return $this->documento_file;
	}

	public function getProponente() {
		return $this->proponente;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	public function setProponente($proponente) {
		$this->proponente = $proponente;
	}

	public function getSoggetto() {
		return $this->proponente->getSoggetto();
	}
	
	function getSoggettoMandatario() {
		return $this->getProponente()->getSoggettoMandatario();
	}	
}
