<?php

namespace CertificazioniBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity()
 * @ORM\Table(name="documenti_registri")
 */
class DocumentoRegistro  extends EntityLoggabileCancellabile {
	
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
	 * @ORM\ManyToOne(targetEntity="CertificazioniBundle\Entity\RegistroDebitori", inversedBy="documenti_registro")
	 * @ORM\JoinColumn(name="registro_id", referencedColumnName="id", nullable=false)
	 */
	private $registro;
	
	public function getId() {
		return $this->id;
	}

	public function getDocumentoFile() {
		return $this->documento_file;
	}

	public function getRegistro() {
		return $this->registro;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	public function setRegistro($registro) {
		$this->registro = $registro;
	}


	
}
