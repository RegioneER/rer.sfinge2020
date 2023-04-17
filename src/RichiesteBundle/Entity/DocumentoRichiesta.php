<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use DocumentoBundle\Entity\DocumentoFile;

/**
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\DocumentoRichiestaRepository")
 * @ORM\Table(name="documenti_richiesta",
 *  indexes={
 *      @ORM\Index(name="idx_documento_richiesta_file_id", columns={"documento_file_id"}),
 * 		@ORM\Index(name="idx_documento_richiesta_id", columns={"documento_richiesta_id"})
 *  })
 */
class DocumentoRichiesta extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
	 * @ORM\JoinColumn(name="documento_file_id", referencedColumnName="id")
     * @var DocumentoFile
	 */
	private $documento_file;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="documenti_richiesta")
	 * @ORM\JoinColumn(name="documento_richiesta_id", referencedColumnName="id", nullable=false)
	 */
	private $richiesta;

	function getId() {
		return $this->id;
	}

	function getDocumentoFile() {
		return $this->documento_file;
	}

	function getRichiesta() {
		return $this->richiesta;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	function setRichiesta($richiesta) {
		$this->richiesta = $richiesta;
	}

	public function getSoggetto() {
		return $this->getRichiesta()->getSoggetto();
	}

}
