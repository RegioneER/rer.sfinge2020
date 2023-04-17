<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Table(name="audit_conti")
 * @ORM\Entity()
 */
class AuditConti extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Audit", inversedBy="audit_conti")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $audit;

	/**
	 * @ORM\OneToMany(targetEntity="DocumentoAuditConti", mappedBy="audit_conti")
	 */
	protected $documenti_audit_conti;
	
	
	public function getId() {
		return $this->id;
	}

	public function getAudit() {
		return $this->audit;
	}

	public function getDocumentiAuditConti() {
		return $this->documenti_audit_conti;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setAudit($audit) {
		$this->audit = $audit;
	}

	public function setDocumentiAuditConti($documenti_audit_conti) {
		$this->documenti_audit_conti = $documenti_audit_conti;
	}

}
