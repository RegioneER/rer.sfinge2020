<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use DocumentoBundle\Entity\DocumentoFile;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documenti_risposta_comunicazione_pagamenti")
 */
class DocumentoRispostaComunicazionePagamento extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * @var DocumentoFile
     */
    private $documento_file;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\RispostaComunicazionePagamento", inversedBy="documenti")
     * @ORM\JoinColumn(nullable=false)
     * @var RispostaComunicazionePagamento
     */
    private $risposta_comunicazione_pagamento;
    
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
    
    function getDocumentoFile() {
        return $this->documento_file;
    }

    function setDocumentoFile($documento_file) {
        $this->documento_file = $documento_file;
    }

    function getRispostaComunicazionePagamento() {
        return $this->risposta_comunicazione_pagamento;
    }

    function setRispostaComunicazionePagamento($rispostaComunicazionePagamento) {
        $this->risposta_comunicazione_pagamento = $rispostaComunicazionePagamento;
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
