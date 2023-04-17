<?php

namespace AuditBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Table(name="audit_documenti_campioni_operazioni")
 * @ORM\Entity()
 */
class DocumentoCampioneOperazione extends EntityLoggabileCancellabile {

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
	 * @ORM\ManyToOne(targetEntity="AuditCampioneOperazione", inversedBy="documenti_campione_operazione")
	 * @ORM\JoinColumn(name="campione_id", referencedColumnName="id", nullable=true)
	 */
	protected $audit_campione_operazione;

	public function getId() {
		return $this->id;
	}

	public function getDocumentoFile() {
		return $this->documento_file;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	public function getAuditCampioneOperazione() {
		return $this->audit_campione_operazione;
	}

	public function setAuditCampioneOperazione($audit_campione_operazione) {
		$this->audit_campione_operazione = $audit_campione_operazione;
	}

}
