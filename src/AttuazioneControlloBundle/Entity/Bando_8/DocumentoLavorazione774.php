<?php

namespace AttuazioneControlloBundle\Entity\Bando_8;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documenti_lavorazioni_774")
 */
class DocumentoLavorazione774 extends EntityLoggabileCancellabile {

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
	private $documentoFile;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Bando_8\EstensionePagamentoBando_8", inversedBy="documenti_lavorazioni",cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $estensione_pagamento_bando8;    

	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $istruttoria_oggetto_pagamento; 
	
	function getId() {
		return $this->id;
	}

	function getDocumentoFile() {
		return $this->documentoFile;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setDocumentoFile($documentoFile) {
		$this->documentoFile = $documentoFile;
	}

	public function getEstensionePagamentoBando7() {
		return $this->estensione_pagamento_bando8;
	}

	public function setEstensionePagamentoBando7($estensione_pagamento_bando8) {
		$this->estensione_pagamento_bando8 = $estensione_pagamento_bando8;
	}

	function getIstruttoriaOggettoPagamento() {
		return $this->istruttoria_oggetto_pagamento;
	}

	function setIstruttoriaOggettoPagamento($istruttoria_oggetto_pagamento) {
		$this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
	}

}
