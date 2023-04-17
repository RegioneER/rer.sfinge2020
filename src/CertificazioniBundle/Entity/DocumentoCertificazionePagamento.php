<?php

namespace CertificazioniBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="CertificazioniBundle\Entity\DocumentoCertificazionePagamentoRepository")
 * @ORM\Table(name="documenti_pagamento_certificazione")
 */
class DocumentoCertificazionePagamento  extends EntityLoggabileCancellabile {
	
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
	 * @ORM\ManyToOne(targetEntity="CertificazioniBundle\Entity\CertificazionePagamento", inversedBy="documenti_certificazione_pagamento")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $certificazione_pagamento;
	
	
	function getId() {
		return $this->id;
	}

	function getDocumentoFile() {
		return $this->documento_file;
	}

	function getCertificazionePagamento() {
		return $this->certificazione_pagamento;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	function setCertificazionePagamento($certificazione_pagamento) {
		$this->certificazione_pagamento = $certificazione_pagamento;
	}

}
