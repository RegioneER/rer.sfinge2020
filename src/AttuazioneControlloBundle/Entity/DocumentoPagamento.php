<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use DocumentoBundle\Entity\DocumentoFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\DocumentoPagamentoRepository")
 * @ORM\Table(name="documenti_pagamenti")
 */
class DocumentoPagamento extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=false)
	 * @Assert\Valid
     * @var DocumentoFile
	 */
	protected $documento_file;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="documenti_pagamento")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $pagamento;
    
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $integrazione;
    
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $nota_integrazione;
    
	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\DocumentoPagamento", inversedBy="integrato_da")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $integrazione_di;     
    
	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\DocumentoPagamento", mappedBy="integrazione_di")
	 */
	protected $integrato_da;    
	
	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $istruttoria_oggetto_pagamento; 
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $nota;
	
    public function getId() {
        return $this->id;
    }

    public function getDocumentoFile(): ?DocumentoFile {
        return $this->documento_file;
    }

    public function getPagamento() {
        return $this->pagamento;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setDocumentoFile($documento_file) {
        $this->documento_file = $documento_file;
    }

    public function setPagamento(Pagamento $pagamento) {
        $this->pagamento = $pagamento;
    }
    
    function getIntegrazione() {
        return $this->integrazione;
    }

    function getNotaIntegrazione() {
        return $this->nota_integrazione;
    }

    function setIntegrazione($integrazione) {
        $this->integrazione = $integrazione;
        return $this;
    }

    function setNotaIntegrazione($nota_integrazione) {
        $this->nota_integrazione = $nota_integrazione;
        return $this;
    }
    
    function getIntegrazioneDi() {
        return $this->integrazione_di;
    }

    function getIntegratoDa() {
        return $this->integrato_da;
    }

    function setIntegrazioneDi($integrazione_di) {
        $this->integrazione_di = $integrazione_di;
        return $this;
    }

    function setIntegratoDa($integrato_da) {
        $this->integrato_da = $integrato_da;
        return $this;
    }
    
    public function getSoggetto() {
		return $this->getPagamento()->getSoggetto();
	}  
		    
	function getIstruttoriaOggettoPagamento() {
		return $this->istruttoria_oggetto_pagamento;
	}

	function setIstruttoriaOggettoPagamento($istruttoria_oggetto_pagamento) {
		$this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
	}
	
    public function isModificabileIntegrazione() {
        if (!is_null($this->getPagamento()->getIntegrazioneDi()) && is_null($this->getIntegrazioneDi())) {
            return false;
        }
        
        return true;
    }    
    
	public function __clone() {	
        if ($this->id) {
			parent::__clone();
            
            $this->integrazione = null;
            $this->nota_integrazione = null;
            $this->documento_file = clone $this->documento_file;
        }
    }
	
	public function getNota() {
		return $this->nota;
	}

	public function setNota($nota) {
		$this->nota = $nota;
	}

}
