<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use DocumentoBundle\Entity\DocumentoFile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\DocumentoContrattoRepository")
 * @ORM\Table(name="documenti_contratto")
 */
class DocumentoContratto extends EntityLoggabileCancellabile {

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
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Contratto", inversedBy="documentiContratto")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $contratto;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $istruttoria_oggetto_pagamento;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $nota;

    function getId() {
        return $this->id;
    }

    /**
     * @return DocumentoFile 
     */
    function getDocumentoFile() {
        return $this->documentoFile;
    }

    function getContratto() {
        return $this->contratto;
    }

    function setId($id) {
        $this->id = $id;
    }

    /**
     * @param DocumentoFile $documentoFile
     */
    function setDocumentoFile($documentoFile) {
        $this->documentoFile = $documentoFile;
    }

    function setContratto($contratto) {
        $this->contratto = $contratto;
    }

    function getIstruttoriaOggettoPagamento() {
        return $this->istruttoria_oggetto_pagamento;
    }

    function setIstruttoriaOggettoPagamento($istruttoria_oggetto_pagamento) {
        $this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
    }
    
    function getNota() {
        return $this->nota;
    }

    function setNota($nota): void {
        $this->nota = $nota;
    }

    public function __clone() {
        if ($this->id) {
            $this->id = NULL;
            $this->documentoFile = $this->documentoFile ? clone $this->documentoFile : NULL;
            $this->istruttoria_oggetto_pagamento = $this->istruttoria_oggetto_pagamento ? clone $this->istruttoria_oggetto_pagamento : NULL;
        }
    }

}
