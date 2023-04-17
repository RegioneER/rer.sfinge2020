<?php
namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
	
/**
 * @ORM\Entity()
 * @ORM\Table(name="audit_requisiti")
 */
class Requisito
{
   /**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=255, nullable=false)
	 * 
	 * @Assert\NotBlank()
	 */
	private $codice;
	
	/**
	 * @ORM\Column(type="string", length=1024, nullable=false)
	 * 
	 * @Assert\NotBlank()
	 */
	private $denominazione;
	
	/** 	 
	 * @ORM\ManyToMany(targetEntity="Organismo", mappedBy="requisiti")
	 */
	private $organismi;
	
	/** 	 
	 * @ORM\OneToMany(targetEntity="AuditRequisito", mappedBy="requisito")
	 */
	private $audit_requisiti;
	
	function getId() {
		return $this->id;
	}

	function getDenominazione() {
		return $this->denominazione;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setDenominazione($denominazione) {
		$this->denominazione = $denominazione;
	}
	
	function getOrganismi() {
		return $this->organismi;
	}

	function setOrganismi($organismi) {
		$this->organismi = $organismi;
	}

	function getCodice() {
		return $this->codice;
	}

	function setCodice($codice) {
		$this->codice = $codice;
	}

	public function getAuditRequisiti() {
		return $this->audit_requisiti;
	}

	public function setAuditRequisiti($audit_requisiti) {
		$this->audit_requisiti = $audit_requisiti;
	}
	
	public function __toString() {
		return $this->codice ."-". $this->denominazione;
	}

}