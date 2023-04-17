<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Annotation as Sfinge;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Entity()
 * @ORM\Table(name="quietanze_giustificativi")
 * @Assert\Callback(callback="validateImportazione",groups={"sanita"})
 */
class QuietanzaGiustificativo extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\GiustificativoPagamento", inversedBy="quietanze")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $giustificativo_pagamento;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    protected $documento_quietanza;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $data_valuta;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $data_quietanza;
    
    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $data_avvenuta_esecuzione;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $numero;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo;
    
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_mandato;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\TipologiaQuietanza")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $tipologia_quietanza;

    function getId() {
        return $this->id;
    }

    /**
     * @return GiustificativoPagamento
     */
    function getGiustificativoPagamento(): GiustificativoPagamento {
        return $this->giustificativo_pagamento;
    }

    function getDocumentoQuietanza() {
        return $this->documento_quietanza;
    }

    function getDataValuta() {
        return $this->data_valuta;
    }

    function getDataQuietanza() {
        return $this->data_quietanza;
    }

    function getNumero() {
        return $this->numero;
    }

    function getTipologiaQuietanza() {
        return $this->tipologia_quietanza;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setGiustificativoPagamento($giustificativo_pagamento) {
        $this->giustificativo_pagamento = $giustificativo_pagamento;
    }

    function setDocumentoQuietanza($documento_quietanza) {
        $this->documento_quietanza = $documento_quietanza;
    }

    function setDataValuta($data_valuta) {
        $this->data_valuta = $data_valuta;
    }

    function setDataQuietanza($data_quietanza) {
        $this->data_quietanza = $data_quietanza;
    }

    function setNumero($numero) {
        $this->numero = $numero;
    }

    function setTipologiaQuietanza($tipologia_quietanza) {
        $this->tipologia_quietanza = $tipologia_quietanza;
    }

    function getImporto() {
        return $this->importo;
    }

    function setImporto($importo) {
        $this->importo = $importo;
    }

    public function getSoggetto() {
        return $this->getGiustificativoPagamento()->getSoggetto();
    }

    public function getProcedura() {
        return $this->getGiustificativoPagamento()->getProcedura();
    }

    public function __clone() {
        if ($this->id) {
            $this->documento_quietanza = clone $this->documento_quietanza;
        }
    }

    public function getQuietanza() {
        return $this;
    }

    public function getPagamento() {
        return $this->getGiustificativoPagamento()->getPagamento();
    }

    public function getRichiesta() {
        return $this->getPagamento()->getRichiesta();
    }

    function getDataAvvenutaEsecuzione() {
        return $this->data_avvenuta_esecuzione;
    }

    function getImportoMandato() {
        return $this->importo_mandato;
    }

    function setDataAvvenutaEsecuzione($data_avvenuta_esecuzione): void {
        $this->data_avvenuta_esecuzione = $data_avvenuta_esecuzione;
    }

    function setImportoMandato($importo_mandato): void {
        $this->importo_mandato = $importo_mandato;
    }

    public function validateImportazione(\Symfony\Component\Validator\Context\ExecutionContextInterface $context) {
        if (\is_null($this->getDocumentoQuietanza())) {
            $context->buildViolation('documento_quietanza non valorizzato')
                    ->atPath('quietanza')
                    ->addViolation();
        }
        
        if (\is_null($this->getTipologiaQuietanza())) {
            $context->buildViolation('tipologia_quietanza non valorizzata')
                    ->atPath('quietanza')
                    ->addViolation();
        }
        
        if (\is_null($this->getDataQuietanza())) {
            $context->buildViolation('data_quietanza non valorizzata')
                    ->atPath('quietanza')
                    ->addViolation();
        }
        
        if (\is_null($this->getImporto())) {
            $context->buildViolation('importo_quietanza non valorizzata')
                    ->atPath('quietanza')
                    ->addViolation();
        }
        
        if (\is_null($this->getDataAvvenutaEsecuzione())) {
            $context->buildViolation('data_avvenuta_esecuzione non valorizzata')
                    ->atPath('quietanza')
                    ->addViolation();
        }
        
        if (\is_null($this->getImportoMandato())) {
            $context->buildViolation('importo_mandato non valorizzata')
                    ->atPath('quietanza')
                    ->addViolation();
        }

    }
}
