<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Entity()
 * @ORM\Table(name="audit_aree_tematiche")
 */
class AreaTematica extends EntityLoggabileCancellabile
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



}