<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Table(name="audit")
 * @ORM\Entity()
 */
class Audit extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="PeriodoContabile", inversedBy="audit")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $periodo_contabile;

	/**
	 * @ORM\ManyToOne(targetEntity="TipoAudit")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $tipo;

	/**
	 * @ORM\OneToMany(targetEntity="AuditOrganismo", mappedBy="audit")
	 */
	private $audit_organismo;

	/**
	 * @ORM\OneToMany(targetEntity="AuditOperazione", mappedBy="audit")
	 */
	private $audit_operazione;

	/**
	 * @ORM\OneToMany(targetEntity="AuditStrategia", mappedBy="audit")
	 */
	private $audit_strategie;

	/**
	 * @ORM\OneToMany(targetEntity="AuditConti", mappedBy="audit")
	 */
	private $audit_conti;

	/**
	 * @ORM\OneToMany(targetEntity="AuditRac", mappedBy="audit")
	 */
	private $audit_rac;

	public function getPeriodoContabile() {
		return $this->periodo_contabile;
	}

	public function getTipo() {
		return $this->tipo;
	}

	public function setPeriodoContabile($periodo_contabile) {
		$this->periodo_contabile = $periodo_contabile;
	}

	public function setTipo($tipo) {
		$this->tipo = $tipo;
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getAuditOrganismo() {
		return $this->audit_organismo;
	}

	public function setAuditOrganismo($audit_organismo) {
		$this->audit_organismo = $audit_organismo;
	}

	public function getAuditOperazione() {
		return $this->audit_operazione;
	}

	public function setAuditOperazione($audit_campione_operazione) {
		$this->audit_operazione = $audit_campione_operazione;
	}

	public function getAuditStrategie() {
		return $this->audit_strategie;
	}

	public function setAuditStrategie($audit_strategie) {
		$this->audit_strategie = $audit_strategie;
	}

	public function getAuditConti() {
		return $this->audit_conti;
	}

	public function setAuditConti($audit_conti) {
		$this->audit_conti = $audit_conti;
	}

	public function getAuditRac() {
		return $this->audit_rac;
	}

	public function setAuditRac($audit_rac) {
		$this->audit_rac = $audit_rac;
	}

}
