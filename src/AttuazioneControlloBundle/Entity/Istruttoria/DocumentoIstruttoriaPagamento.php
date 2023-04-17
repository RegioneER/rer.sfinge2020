<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use AttuazioneControlloBundle\Entity\Pagamento;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use DocumentoBundle\Entity\DocumentoFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="documenti_istruttoria_pagamenti")
 */
class DocumentoIstruttoriaPagamento extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="documentofile_id", referencedColumnName="id")
     * @var DocumentoFile
     */
    private $documentoFile;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_richiesto;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_ricevuto;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_scadenza;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="documenti_istruttoria")
     * @ORM\JoinColumn(name="pagamento_id", nullable=false)
	 * @var Pagamento
     */
    protected $pagamento;

    public function getId() {
        return $this->id;
    }

    public function getDocumentoFile(): ?DocumentoFile {
        return $this->documentoFile;
    }

    public function getDataRichiesto(): ?\DateTime {
        return $this->data_richiesto;
    }

    public function getDataRicevuto(): ?\DateTime {
        return $this->data_ricevuto;
    }

    public function getDataScadenza(): ?\DateTime {
        return $this->data_scadenza;
    }

    public function setDocumentoFile(?DocumentoFile $documentoFile) {
        $this->documentoFile = $documentoFile;
    }

    public function setDataRichiesto(?\DateTime $data_richiesto) {
        $this->data_richiesto = $data_richiesto;
    }

    public function setDataRicevuto(?\DateTime $data_ricevuto) {
        $this->data_ricevuto = $data_ricevuto;
    }

    public function setDataScadenza(?\DateTime $data_scadenza) {
        $this->data_scadenza = $data_scadenza;
    }

    public function getPagamento(): ?Pagamento {
        return $this->pagamento;
    }

    public function setPagamento(Pagamento $pagamento) {
        $this->pagamento = $pagamento;
    }
}
