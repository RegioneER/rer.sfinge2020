<?php
namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
	
/**
 * @ORM\Entity(repositoryClass="AuditBundle\Entity\OrganismoRepository")
 * @ORM\Table(name="audit_organismi")
 */
class Organismo
{
   /**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=1024, nullable=false)
	 * 
	 * @Assert\NotBlank()
	 */
	private $denominazione;
	
	/** 	 
	 * @ORM\ManyToMany(targetEntity="Requisito", inversedBy="organismi")
	 * @ORM\JoinTable(name="audit_organismi_requisiti")
	 */
	private $requisiti;
	
	/**
	 * @ORM\Column(type="string", length=1024, nullable=false)
	 * 
	 * @Assert\NotBlank()
	 */
	private $codice;
			
	function __construct() {
		$this->requisiti = new ArrayCollection();
	}

	
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

	function getRequisiti() {
		return $this->requisiti;
	}

	function setRequisiti($requisiti) {
		$this->requisiti = $requisiti;
	}

	function getCodice() {
		return $this->codice;
	}

	function setCodice($codice) {
		$this->codice = $codice;
	}

	function __toString() {
		return $this->getCodice();
	}
}