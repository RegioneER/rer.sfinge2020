<?php

namespace AnagraficheBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use DocumentoBundle\Entity\DocumentoFile;

/**
 * @ORM\Entity(repositoryClass="AnagraficheBundle\Entity\DocumentoPersonaleRepository")
 * @ORM\Table(name="documenti_personale")
 */
class DocumentoPersonale extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
     * @var DocumentoFile
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
	 * @ORM\JoinColumn(name="documento_file_id", referencedColumnName="id")
	 */
	private $documento_file;

	/**
	 * @var Personale
	 * @ORM\ManyToOne(targetEntity="AnagraficheBundle\Entity\Personale", inversedBy="documenti_personale")
	 * @ORM\JoinColumn(name="documento_personale_id", referencedColumnName="id", nullable=false)
	 */
	private $personale;

	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $istruttoria_oggetto_pagamento; 

	
	function getId() {
		return $this->id;
	}

	function getDocumentoFile() {
		return $this->documento_file;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	public function getPersonale() {
		return $this->personale;
	}

	public function setPersonale($personale) {
		$this->personale = $personale;
	}
	
	function getIstruttoriaOggettoPagamento() {
		return $this->istruttoria_oggetto_pagamento;
	}

	function setIstruttoriaOggettoPagamento($istruttoria_oggetto_pagamento) {
		$this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
	}
	
}
