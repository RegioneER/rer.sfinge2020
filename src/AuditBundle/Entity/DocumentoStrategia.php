<?php

namespace AuditBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Table(name="audit_documenti_strategie")
 * @ORM\Entity()
 */
class DocumentoStrategia extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $documento_file;

	/**
	 * @ORM\ManyToOne(targetEntity="AuditStrategia", inversedBy="documenti_strategia")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $audit_strategia;

	public function getId() {
		return $this->id;
	}

	public function getDocumentoFile() {
		return $this->documento_file;
	}

	public function getAuditStrategia() {
		return $this->audit_strategia;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	public function setAuditStrategia($audit_strategia) {
		$this->audit_strategia = $audit_strategia;
	}


}
