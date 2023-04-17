<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Table(name="audit_rac")
 * @ORM\Entity()
 */
class AuditRac extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Audit", inversedBy="audit_rac")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $audit;

	/**
	 * @ORM\OneToMany(targetEntity="DocumentoAuditRac", mappedBy="audit_rac")
	 */
	protected $documenti_audit_rac;
	
	
	public function getId() {
		return $this->id;
	}

	public function getAudit() {
		return $this->audit;
	}

	public function getDocumentiAuditRac() {
		return $this->documenti_audit_rac;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setAudit($audit) {
		$this->audit = $audit;
	}

	public function setDocumentiAuditRac($documenti_audit_rac) {
		$this->documenti_audit_rac = $documenti_audit_rac;
	}



}
