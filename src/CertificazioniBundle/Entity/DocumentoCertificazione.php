<?php

namespace CertificazioniBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="CertificazioniBundle\Entity\DocumentoCertificazioneRepository")
 * @ORM\Table(name="documenti_certificazione")
 */
class DocumentoCertificazione  extends EntityLoggabileCancellabile {
	
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
	 * @ORM\ManyToOne(targetEntity="CertificazioniBundle\Entity\Certificazione", inversedBy="documenti_certificazione")
	 * @ORM\JoinColumn(name="certificazione_id", referencedColumnName="id", nullable=false)
	 */
	private $certificazione;
	
	
	function getId() {
		return $this->id;
	}

	function getDocumentoFile() {
		return $this->documento_file;
	}

	function getCertificazione() {
		return $this->certificazione;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	function setCertificazione($certificazione) {
		$this->certificazione = $certificazione;
	}
	
	
}
