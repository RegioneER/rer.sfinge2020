<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documenti_esito_istruttoria")
 */
class DocumentoEsitoIstruttoria extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $documento_file;	
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $descrizione;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\EsitoIstruttoriaPagamento", inversedBy="documenti_esito_istruttoria")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $esito_istruttoria_pagamento;	
	
	function getId() {
		return $this->id;
	}

	function setId($id) {
		$this->id = $id;
	}
	
	function getDocumentoFile() {
		return $this->documento_file;
	}

	function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	public function getDescrizione() {
		return $this->descrizione;
	}

	public function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;
	}
	
	function getEsitoIstruttoriaPagamento() {
		return $this->esito_istruttoria_pagamento;
	}

	function setEsitoIstruttoriaPagamento($esito_istruttoria_pagamento) {
		$this->esito_istruttoria_pagamento = $esito_istruttoria_pagamento;
	}
	
}
