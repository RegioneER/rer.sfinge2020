<?php

namespace AuditBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Table(name="audit_documenti_rac")
 * @ORM\Entity()
 */
class DocumentoAuditRac extends EntityLoggabileCancellabile {

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
	 * @ORM\ManyToOne(targetEntity="AuditRac", inversedBy="documenti_audit_rac")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $audit_rac;

	public function getId() {
		return $this->id;
	}

	public function getDocumentoFile() {
		return $this->documento_file;
	}

	public function getAuditRac() {
		return $this->audit_rac;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	public function setAuditRac($audit_rac) {
		$this->audit_rac = $audit_rac;
	}




}
