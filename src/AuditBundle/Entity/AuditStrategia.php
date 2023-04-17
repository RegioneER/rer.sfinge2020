<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Table(name="audit_strategie")
 * @ORM\Entity()
 */
class AuditStrategia extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Audit", inversedBy="audit_strategie")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $audit;

	/**
	 * @ORM\OneToMany(targetEntity="DocumentoStrategia", mappedBy="audit_strategia")
	 */
	protected $documenti_strategia;
	
	public function getId() {
		return $this->id;
	}

	public function getAudit() {
		return $this->audit;
	}

	public function getDocumentiStrategia() {
		return $this->documenti_strategia;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setAudit($audit) {
		$this->audit = $audit;
	}

	public function setDocumentiStrategia($documenti_strategia) {
		$this->documenti_strategia = $documenti_strategia;
	}


}
