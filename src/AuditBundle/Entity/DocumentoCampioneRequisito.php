<?php

namespace AuditBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Table(name="audit_documenti_campioni_requisiti")
 * @ORM\Entity()
 */
class DocumentoCampioneRequisito extends EntityLoggabileCancellabile {

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
	 * @ORM\ManyToOne(targetEntity="AuditCampione", inversedBy="documenti_campione_requisito")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $audit_campione;

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

	public function getAuditCampione() {
		return $this->audit_campione;
	}

	public function setAuditCampione($audit_campione) {
		$this->audit_campione = $audit_campione;
	}

}
