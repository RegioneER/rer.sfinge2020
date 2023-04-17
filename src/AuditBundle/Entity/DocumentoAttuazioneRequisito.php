<?php

namespace AuditBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Table(name="audit_documenti_attuazione_requisiti")
 * @ORM\Entity()
 */
class DocumentoAttuazioneRequisito extends EntityLoggabileCancellabile {

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
	 * @ORM\ManyToOne(targetEntity="AuditRequisito", inversedBy="documenti_attuazione_requisito")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $audit_requisito;

    public function getId() {
        return $this->id;
    }

    public function getDocumentoFile() {
        return $this->documento_file;
    }

    public function getAuditRequisito() {
        return $this->audit_requisito;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setDocumentoFile($documento_file) {
        $this->documento_file = $documento_file;
    }

    public function setAuditRequisito($audit_requisito) {
        $this->audit_requisito = $audit_requisito;
    }

}
