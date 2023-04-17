<?php

namespace CertificazioniBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="CertificazioniBundle\Entity\DocumentoCertificazioneChiusuraRepository")
 * @ORM\Table(name="documenti_certificazione_chiusura")
 */
class DocumentoCertificazioneChiusura  extends EntityLoggabileCancellabile {
	
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
	 * @ORM\ManyToOne(targetEntity="CertificazioniBundle\Entity\CertificazioneChiusura", inversedBy="documenti_chiusura")
	 * @ORM\JoinColumn(name="chiusura_id", referencedColumnName="id", nullable=false)
	 */
	private $chiusura;
	
	
	function getId() {
		return $this->id;
	}

	public function getDocumentoFile() {
		return $this->documento_file;
	}

	public function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getChiusura() {
		return $this->chiusura;
	}

	public function setChiusura($chiusura) {
		$this->chiusura = $chiusura;
	}
	
}
