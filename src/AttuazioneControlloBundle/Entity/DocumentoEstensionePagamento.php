<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints AS Assert;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\DocumentoEstensionePagamentoRepository")
 * @ORM\Table(name="documenti_estensioni_pagamenti")
 *  })
 */
class DocumentoEstensionePagamento extends EntityLoggabileCancellabile {
	
	const TIPO_DOCUMENTO_PERSONALE = 'personale';
	const TIPO_DOCUMENTO_GENERALE = 'generale';	

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $documento_file;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\EstensionePagamento", inversedBy="documenti",cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $estensione_pagamento;   
	
	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $istruttoria_oggetto_pagamento; 
	
	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente")
	 * @ORM\JoinColumn(nullable=true)
	 * @Assert\NotNull()
	 */
	protected $proponente;
	
	/**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
	protected $tipo;

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

    public function getEstensionePagamento() {
        return $this->estensione_pagamento;
    }

    public function setEstensionePagamento($estensione_pagamento) {
        $this->estensione_pagamento = $estensione_pagamento;
    }

	public function getIstruttoriaOggettoPagamento() {
		return $this->istruttoria_oggetto_pagamento;
	}

	public function setIstruttoriaOggettoPagamento($istruttoria_oggetto_pagamento) {
		$this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
	}
	
	public function getProponente() {
		return $this->proponente;
	}

	public function setProponente($proponente) {
		$this->proponente = $proponente;
	}
	
	public function getTipo() {
		return $this->tipo;
	}

	public function setTipo($tipo) {
		$this->tipo = $tipo;
	}
	
	public function getSoggetto() {
		return $this->getEstensionePagamento()->getPagamento()->getSoggetto();
	}  

}
