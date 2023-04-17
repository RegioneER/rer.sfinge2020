<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Validator\Constraints\ValidaLunghezza;
use AttuazioneControlloBundle\Entity\Pagamento;

/**
 * @ORM\Entity(repositoryClass="CertificazioniBundle\Repository\CertificazionePagamentoRepository")
 * @ORM\Table(name="certificazioni_pagamenti")
 */
class CertificazionePagamento {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="CertificazioniBundle\Entity\Certificazione", inversedBy="pagamenti")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $certificazione;
    
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="certificazioni")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $pagamento;
    
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $importo;
    
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_taglio;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $tipologia_taglio;

    /**
     * @ORM\Column(name="nota_taglio", type="text", nullable=true)
     */
    private $nota_taglio;

    protected $selezionato;
    
    /**
     * @var boolean $aiuto_di_stato
     * @ORM\Column(type="boolean", name="aiuto_di_stato", nullable=true)
     */
    protected $aiuto_di_stato;
    
    /**
     * @var boolean $strumento_finanziario
     * @ORM\Column(type="boolean", name="strumento_finanziario", nullable=true)
     */
    protected $strumento_finanziario;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
     * @ORM\JoinColumn(name="documento_file_id", referencedColumnName="id")
     */
    protected $documento_file_id;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $nota_decertificazione;

    /**
     * @ORM\OneToMany(targetEntity="CertificazioniBundle\Entity\DocumentoCertificazionePagamento", mappedBy="certificazione_pagamento", cascade={"persist"})
     */
    protected $documenti_certificazione_pagamento;   
	
	 /**
     * @var boolean $ritiro
     * @ORM\Column(type="boolean", name="ritiro", nullable=true)
     */
    protected $ritiro;
	
	 /**
     * @var boolean $recupero
     * @ORM\Column(type="boolean", name="recupero", nullable=true)
     */
    protected $recupero;
	
	/**
     * @var boolean $irregolarita
     * @ORM\Column(type="boolean", name="irregolarita", nullable=true)
     */
    protected $irregolarita;
	
		
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $articolo_137;
	
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $segnalazione_ada;
	
	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $anno_contabile_precedente;
	
	 /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_irregolare;

	public function __construct() {
        
	}
    
    public function getId() {
        return $this->id;
    }

    /**
     * @return Certificazione
     */
    public function getCertificazione() {
        return $this->certificazione;
    }

    /**
     * @return Pagamento
     */
    public function getPagamento() {
        return $this->pagamento;
    }

    public function getImporto() {
        return $this->importo;
    }

    public function getSelezionato() {
        return $this->selezionato;
    }

    public function getAiutoDiStato() {
        return $this->aiuto_di_stato;
    }

    public function getStrumentoFinanziario() {
        return $this->strumento_finanziario;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setCertificazione($certificazione) {
        $this->certificazione = $certificazione;
        return $this;
    }

    public function setPagamento($pagamento) {
        $this->pagamento = $pagamento;
        return $this;
    }

    public function setImporto($importo) {
        $this->importo = $importo;
        return $this;
    }

    public function setSelezionato($selezionato) {
        $this->selezionato = $selezionato;
        return $this;
    }

    public function setAiutoDiStato($aiuto_di_stato) {
        $this->aiuto_di_stato = $aiuto_di_stato;
        return $this;
    }

    public function setStrumentoFinanziario($strumento_finanziario) {
        $this->strumento_finanziario = $strumento_finanziario;
        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(\Symfony\Component\Validator\Context\ExecutionContextInterface $context)
    {
        if ($this->getSelezionato() && is_null($this->getImporto())) {
            $context->buildViolation("L'importo deve essere indicato se il pagamento è associato alla certificazione")
                ->atPath('importo')
                ->addViolation();
        }
    }

    /**
     * Set importo_taglio
     *
     * @param string $importoTaglio
     * @return CertificazionePagamento
     */
    public function setImportoTaglio($importoTaglio)
    {
        $this->importo_taglio = $importoTaglio;

        return $this;
    }

    /**
     * Get importo_taglio
     *
     * @return string 
     */
    public function getImportoTaglio()
    {
        return $this->importo_taglio;
    }

    /**
     * Set tipologia_taglio
     *
     * @param string $tipologiaTaglio
     * @return CertificazionePagamento
     */
    public function setTipologiaTaglio($tipologiaTaglio)
    {
        $this->tipologia_taglio = $tipologiaTaglio;

        return $this;
    }

    /**
     * Get tipologia_taglio
     *
     * @return string 
     */
    public function getTipologiaTaglio()
    {
        return $this->tipologia_taglio;
    }

    /**
     * Set nota_taglio
     *
     * @param string $notaTaglio
     * @return CertificazionePagamento
     */
    public function setNotaTaglio($notaTaglio)
    {
        $this->nota_taglio = $notaTaglio;

        return $this;
    }

    /**
     * Get nota_taglio
     *
     * @return string 
     */
    public function getNotaTaglio()
    {
        return $this->nota_taglio;
    }

    /**
     * Set documento_file_id
     *
     * @param \DocumentoBundle\Entity\DocumentoFile $documentoFileId
     * @return CertificazionePagamento
     */
    public function setDocumentoFileId(\DocumentoBundle\Entity\DocumentoFile $documentoFileId = null)
    {
        $this->documento_file_id = $documentoFileId;

        return $this;
    }

    /**
     * Get documento_file_id
     *
     * @return \DocumentoBundle\Entity\DocumentoFile 
     */
    public function getDocumentoFileId()
    {
        return $this->documento_file_id;
    }
    
    public function getNotaDecertificazione() {
        return $this->nota_decertificazione;
    }

    public function setNotaDecertificazione($nota_decertificazione) {
        $this->nota_decertificazione = $nota_decertificazione;
        return $this;
    }
 
    public function getDocumentiCertificazionePagamento() {
        return $this->documenti_certificazione_pagamento;
    }

    public function setDocumentiCertificazionePagamento($documenti_certificazione_pagamento) {
        $this->documenti_certificazione_pagamento = $documenti_certificazione_pagamento;
        return $this;
    }
	
	public function getRitiro() {
		return $this->ritiro;
	}

	public function getRecupero() {
		return $this->recupero;
	}

	public function setRitiro($ritiro) {
		$this->ritiro = $ritiro;
	}

	public function setRecupero($recupero) {
		$this->recupero = $recupero;
	}
	
	public function isRitiro() {
		return $this->ritiro == true;
	}
	
	public function isRecupero() {
		return $this->recupero == true;
	}
	
	public function getIrregolarita() {
		return $this->irregolarita;
	}

	public function setIrregolarita($irregolarita) {
		$this->irregolarita = $irregolarita;
	}
	
	public function isIrregolarita() {
		return $this->irregolarita == true;
	}
	
	public function getArticolo137() {
		return $this->articolo_137;
	}

	public function setArticolo137($articolo_137) {
		$this->articolo_137 = $articolo_137;
	}
	
	public function isArticolo137() {
		return $this->articolo_137 == true;
	}
	
	public function getAnnoContabilePrecedente() {
		return $this->anno_contabile_precedente;
	}

	public function setAnnoContabilePrecedente($anno_contabile_precedente) {
		$this->anno_contabile_precedente = $anno_contabile_precedente;
	}
	
	public function getSegnalazioneAda() {
		return $this->segnalazione_ada;
	}

	public function setSegnalazioneAda($segnalazione_ada) {
		$this->segnalazione_ada = $segnalazione_ada;
	}

	public function getImportoIrregolare() {
		return $this->importo_irregolare;
	}

	public function setImportoIrregolare($importo_irregolare) {
		$this->importo_irregolare = $importo_irregolare;
	}
    
    public function getChiusuraAnni() {
        if(!is_null($this->certificazione->getChiusura())) {
            return $this->certificazione->getChiusura()->getIntervalliAnni();
        } else {
            return '-';
        }
        
    }

}
