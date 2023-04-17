<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\DocumentoBandoRepository")
 * @ORM\Table(name="documenti_bando",
 *  indexes={
 *      @ORM\Index(name="idx_documento_bando_file_id", columns={"documento_file_id"}),
 *		@ORM\Index(name="idx_documento_bando_id", columns={"documento_bando_id"})
 *  })
 */
class DocumentoBando extends EntityLoggabileCancellabile {

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
	private $documentoFile;

	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Bando", inversedBy="documentoBando")
	 * @ORM\JoinColumn(name="documento_bando_id", referencedColumnName="id", nullable=false)
	 */
	private $bando;

	function getId() {
		return $this->id;
	}

	function getDocumentoFile() {
		return $this->documentoFile;
	}

	function getBando() {
		return $this->bando;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setDocumentoFile($documentoFile) {
		$this->documentoFile = $documentoFile;
	}

	function setBando($bando) {
		$this->bando = $bando;
	}


}
