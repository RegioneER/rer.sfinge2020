<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documenti_giustificativo")
 *  })
 */
class DocumentoGiustificativo extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="documento_file_id", referencedColumnName="id")
     * @Assert\Valid
     * 
     */
    private $documento_file;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\GiustificativoPagamento", inversedBy="documenti_giustificativo")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $giustificativo_pagamento;   
    
    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $istruttoria_oggetto_pagamento; 
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $nota;

    public function __clone() {
        if ($this->id) {
            parent::__clone();

            $this->istruttoria_oggetto_pagamento = null;
            $this->documento_file = clone $this->documento_file;
        }
    }

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
    
    public function getNota() {
        return $this->nota;
    }

    public function setNota($nota) {
        $this->nota = $nota;
    }
}
