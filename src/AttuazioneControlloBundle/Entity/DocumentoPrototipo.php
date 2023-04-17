<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documenti_prototipi")
 *  })
 */
class DocumentoPrototipo extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
	 * @ORM\JoinColumn()
	 */
	private $documento_file;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\GiustificativoPagamento", inversedBy="documenti_prototipo")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $giustificativo_pagamento;   
	
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

    public function getGiustificativoPagamento() {
        return $this->giustificativo_pagamento;
    }

    public function setGiustificativoPagamento($giustificativo_pagamento) {
        $this->giustificativo_pagamento = $giustificativo_pagamento;
    }
	
	public function getIstruttoriaOggettoPagamento() {
		return $this->istruttoria_oggetto_pagamento;
	}

	public function setIstruttoriaOggettoPagamento($istruttoria_oggetto_pagamento) {
		$this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
	}

}
