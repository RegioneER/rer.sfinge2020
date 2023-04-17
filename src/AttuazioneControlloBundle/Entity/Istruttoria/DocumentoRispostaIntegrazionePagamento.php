<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use DocumentoBundle\Entity\DocumentoFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documenti_risposta_integrazione_pagamenti")
 */
class DocumentoRispostaIntegrazionePagamento extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 * @Assert\Valid
	 */
	private $documento_file;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\RispostaIntegrazionePagamento", inversedBy="documenti")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $risposta_integrazione;
	
    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente")
     * @ORM\JoinColumn(nullable=true)
     */
    private $proponente;	
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $descrizione;

	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $istruttoria_oggetto_pagamento; 
	
	function getId() {
		return $this->id;
	}

	function setId($id) {
		$this->id = $id;
	}
	
	function getDocumentoFile(): ?DocumentoFile {
		return $this->documento_file;
	}

	function setDocumentoFile(?DocumentoFile $documento_file) {
		$this->documento_file = $documento_file;
	}

	function getRispostaIntegrazione() {
		return $this->risposta_integrazione;
	}

	function setRispostaIntegrazione($risposta_integrazione) {
		$this->risposta_integrazione = $risposta_integrazione;
	}
	
	function getProponente() {
		return $this->proponente;
	}

	function setProponente($proponente) {
		$this->proponente = $proponente;
	}

	public function getDescrizione() {
		return $this->descrizione;
	}

	public function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;
	}

	public function getIstruttoriaOggettoPagamento() {
		return $this->istruttoria_oggetto_pagamento;
	}

	public function setIstruttoriaOggettoPagamento($istruttoria_oggetto_pagamento) {
		$this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
	}
	
}
