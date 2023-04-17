<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use DocumentoBundle\Entity\DocumentoFile;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documenti_risposta_richiesta_chiarimenti")
 */
class DocumentoRispostaRichiestaChiarimenti extends EntityLoggabileCancellabile {

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
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti", inversedBy="documenti")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $risposta_richiesta_chiarimenti;
	
    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente")
     * @ORM\JoinColumn(nullable=true)
     */
    private $proponente;	
	
	/**
	 * @var string|null
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $descrizione;

	/**
	 * @var IstruttoriaOggettoPagamento|null
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $istruttoria_oggetto_pagamento;

	public function __construct()
	{
		$this->documento_file = new DocumentoFile();	
	}
	
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

	function getRispostaRichiestaChiarimenti() {
		return $this->risposta_richiesta_chiarimenti;
	}

	function setRispostaRichiestaChiarimenti($risposta_richiesta_chiarimenti) {
		$this->risposta_richiesta_chiarimenti = $risposta_richiesta_chiarimenti;
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
