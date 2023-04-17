<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\DocumentiOOIIRepository")
 * @ORM\Table(name="documenti_ooii")
 */
class DocumentiOOII extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
	 * @ORM\JoinColumn(name="documento_file_id", referencedColumnName="id")
	 */
	private $documento_file;

	/**
	 * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\OrganismoIntermedio", inversedBy="documenti_ooii")
	 * @ORM\JoinColumn(name="documento_ooii_id", referencedColumnName="id", nullable=false)
	 */
	private $organismo_intermedio;
	
	
	function getId() {
		return $this->id;
	}

	function getDocumentoFile() {
		return $this->documento_file;
	}

	function getOrganismoIntermedio() {
		return $this->organismo_intermedio;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	function setOrganismoIntermedio($organismo_intermedio) {
		$this->organismo_intermedio = $organismo_intermedio;
	}


	
}
