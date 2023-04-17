<?php

namespace AuditBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Table(name="audit_documenti_attuazione_organismi")
 * @ORM\Entity()
 */
class DocumentoAttuazioneOrganismo extends EntityLoggabileCancellabile {

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
	 * @ORM\ManyToOne(targetEntity="AuditOrganismo", inversedBy="documenti_attuazione_organismo")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $audit_organismo;

	public function getId() {
		return $this->id;
	}

	public function getDocumentoFile() {
		return $this->documento_file;
	}

	public function getAuditOrganismo() {
		return $this->audit_organismo;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	public function setAuditOrganismo($audit_organismo) {
		$this->audit_organismo = $audit_organismo;
	}



}
