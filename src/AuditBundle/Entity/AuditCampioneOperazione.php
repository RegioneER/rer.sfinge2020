<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="audit_campioni_operazioni")
 * @ORM\Entity(repositoryClass="AuditBundle\Entity\AuditCampioneOperazioneRepository")
 */
class AuditCampioneOperazione {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AuditOperazione", inversedBy="campioni")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $audit_operazione;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="audit_campioni_operazioni")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $richiesta;
	protected $selezionato;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $conforme;    
    
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $altro_taglio_pre_contraddittorio;   
    
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $altro_taglio_post_contraddittorio;       
     
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $taglio_ada_proposto;
    
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_inizio_contraddittorio;   
    
 	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_fine_contraddittorio;      
    
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $taglio_contraddittorio;   
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $spesa_irregolare_pre_contraddittorio;   
    
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $spesa_irregolare_post_contraddittorio;   
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $contributo_irregolare_pre_contraddittorio;   
    
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $contributo_irregolare_post_contraddittorio;   
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $spesa_cuscinetto;   
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $contributo_pubblico_cuscinetto; 

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $note;   
    
	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $sede_legale_controllo;
    
 	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_sopralluogo;  
    
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $operazione_conclusa;     
    
	/**
	 * @ORM\OneToMany(targetEntity="AuditCampioneGiustificativo", mappedBy="audit_campione_operazione", cascade={"persist", "remove"})
	 */
	protected $campioni;
	protected $campioni_estesi;    
    
	/**
	 * @ORM\ManyToOne(targetEntity="Verificatore")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $verificatore;
	
	/**
	 * @ORM\OneToMany(targetEntity="AuditBundle\Entity\DocumentoCampioneOperazione", mappedBy="audit_campione_operazione")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $documenti_campione_operazione;
    
    public $percentuale_giustificativi;
    
	/**
	 * @ORM\Column(name="azione_correttiva", type="string", length=50, nullable=true)
	 */
	protected $azione_correttiva; 
     
 	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_termine_followup;     
	
	/**
	 * @ORM\ManyToOne(targetEntity="TipoIrregolarita")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $tipo_irregolarita;
	
	
	/**
	 * @ORM\ManyToOne(targetEntity="NaturaIrregolarita")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $natura_irregolarita;
    
	public function getId() {
		return $this->id;
	}

	public function getAuditOperazione() {
		return $this->audit_operazione;
	}

	public function getSelezionato() {
		return $this->selezionato;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setAuditOperazione($audit_operazione) {
		$this->audit_operazione = $audit_operazione;
	}

	public function setSelezionato($selezionato) {
		$this->selezionato = $selezionato;
	}

    public function getConforme() {
        return $this->conforme;
    }

    public function getAltroTaglioPreContraddittorio() {
        return $this->altro_taglio_pre_contraddittorio;
    }

    public function getAltroTaglioPostContraddittorio() {
        return $this->altro_taglio_post_contraddittorio;
    }

    public function getTaglioAdaProposto() {
        return $this->taglio_ada_proposto;
    }

    public function getDataInizioContraddittorio() {
        return $this->data_inizio_contraddittorio;
    }

    public function getDataFineContraddittorio() {
        return $this->data_fine_contraddittorio;
    }

    public function getTaglioContraddittorio() {
        return $this->taglio_contraddittorio;
    }

    public function getNote() {
        return $this->note;
    }

    public function setConforme($conforme) {
        $this->conforme = $conforme;
    }

    public function setAltroTaglioPreContraddittorio($altro_taglio_pre_contraddittorio) {
        $this->altro_taglio_pre_contraddittorio = $altro_taglio_pre_contraddittorio;
    }

    public function setAltroTaglioPostContraddittorio($altro_taglio_post_contraddittorio) {
        $this->altro_taglio_post_contraddittorio = $altro_taglio_post_contraddittorio;
    }

    public function setTaglioAdaProposto($taglio_ada_proposto) {
        $this->taglio_ada_proposto = $taglio_ada_proposto;
    }

    public function setDataInizioContraddittorio($data_inizio_contraddittorio) {
        $this->data_inizio_contraddittorio = $data_inizio_contraddittorio;
    }

    public function setDataFineContraddittorio($data_fine_contraddittorio) {
        $this->data_fine_contraddittorio = $data_fine_contraddittorio;
    }

    public function setTaglioContraddittorio($taglio_contraddittorio) {
        $this->taglio_contraddittorio = $taglio_contraddittorio;
    }

    public function setNote($note) {
        $this->note = $note;
    }

	public function getRichiesta() {
		return $this->richiesta;
	}

	public function setRichiesta($richiesta) {
		$this->richiesta = $richiesta;
	}
	
    public function getSedeLegaleControllo() {
        return $this->sede_legale_controllo;
    }

    public function getDataSopralluogo() {
        return $this->data_sopralluogo;
    }

    public function setSedeLegaleControllo($sede_legale_controllo) {
        $this->sede_legale_controllo = $sede_legale_controllo;
    }

    public function setDataSopralluogo($data_sopralluogo) {
        $this->data_sopralluogo = $data_sopralluogo;
    }

    public function getOperazioneConclusa() {
        return $this->operazione_conclusa;
    }

    public function setOperazioneConclusa($operazione_conclusa) {
        $this->operazione_conclusa = $operazione_conclusa;
    }
    
    public function getCampioni() {
        return $this->campioni;
    }

    public function getCampioniEstesi() {
        return $this->campioni_estesi;
    }

    public function setCampioni($campioni) {
        $this->campioni = $campioni;
    }

    public function setCampioniEstesi($campioni_estesi) {
        $this->campioni_estesi = $campioni_estesi;
    }

	public function addCampioneEsteso($campione) {
		$this->campioni_estesi[] = $campione;
		$campione->setAuditCampioneOperazione($this);;
	}

	public function addCampione($campione) {
		$this->campioni->add($campione);
		$campione->setAuditCampioneOperazione($this);
	} 
    
    public function getVerificatore() {
        return $this->verificatore;
    }

    public function setVerificatore($verificatore) {
        $this->verificatore = $verificatore;
    }
	
	public function getDocumentiCampioneOperazione() {
		return $this->documenti_campione_operazione;
	}

	public function setDocumentiCampioneOperazione($documenti_campione_operazione) {
		$this->documenti_campione_operazione = $documenti_campione_operazione;
	}
    
    public function getAzioneCorrettiva() {
        return $this->azione_correttiva;
    }

    public function setAzioneCorrettiva($azione_correttiva) {
        $this->azione_correttiva = $azione_correttiva;
    }
    
    public function getDataTermineFollowup() {
        return $this->data_termine_followup;
    }

    public function setDataTermineFollowup($data_termine_followup) {
        $this->data_termine_followup = $data_termine_followup;
    }
	
	public function getTipoIrregolarita() {
		return $this->tipo_irregolarita;
	}

	public function getNaturaIrregolarita() {
		return $this->natura_irregolarita;
	}

	public function setTipoIrregolarita($tipo_irregolarita) {
		$this->tipo_irregolarita = $tipo_irregolarita;
	}

	public function setNaturaIrregolarita($natura_irregolarita) {
		$this->natura_irregolarita = $natura_irregolarita;
	}

	public function getSpesaIrregolarePreContraddittorio() {
		return $this->spesa_irregolare_pre_contraddittorio;
	}

	public function getSpesaIrregolarePostContraddittorio() {
		return $this->spesa_irregolare_post_contraddittorio;
	}

	public function setSpesaIrregolarePreContraddittorio($spesa_irregolare_pre_contraddittorio) {
		$this->spesa_irregolare_pre_contraddittorio = $spesa_irregolare_pre_contraddittorio;
	}

	public function setSpesaIrregolarePostContraddittorio($spesa_irregolare_post_contraddittorio) {
		$this->spesa_irregolare_post_contraddittorio = $spesa_irregolare_post_contraddittorio;
	}

	/* INIZIO FORMULE TAGLIO PRE CONTRADDITTORIO*/
    public function calcolaTaglioPreContraddittorioCampioni() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $taglio = $campione->getTaglioPreContraddittorio();
            
            if (!is_null($taglio)) {
                $importo += $taglio;
            }
        }
        
        return $importo;
    }
	
	public function calcolaTaglioPreContraddittorioTotale() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $taglio = $campione->getTaglioPreContraddittorio();
            
            if (!is_null($taglio)) {
                $importo += $taglio;
            }
        }
        
        return $importo + $this->altro_taglio_pre_contraddittorio;
    }
	/* FINE FORMULE TAGLIO PRE CONTRADDITTORIO*/
    
	/* INIZIO FORMULE TAGLIO POST CONTRADDITTORIO*/
    public function calcolaTaglioPostContraddittorioCampioni() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $taglio = $campione->getTaglioPostContraddittorio();
            
            if (!is_null($taglio)) {
                $importo += $taglio;
            }
        }
        
        return $importo;        
    }
	
	public function calcolaTaglioPostContraddittorioTotale() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $taglio = $campione->getTaglioPostContraddittorio();
            
            if (!is_null($taglio)) {
                $importo += $taglio;
            }
        }
        
        return $importo + $this->altro_taglio_post_contraddittorio;        
    }
	
	/* FINE FORMULE TAGLIO POST CONTRADDITTORIO*/
	
	/* INIZIO FORMULE SPESA IRREGOLARE PRE CONTRADDITTORIO*/
	public function calcolaIrregolarePreContraddittorioCampioni() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $irregolare = $campione->getSpesaIrregolarePreContraddittorio();
            
            if (!is_null($irregolare)) {
                $importo += $irregolare;
            }
        }
        
        return $importo;
    }
	
	public function calcolaIrregolarePreContraddittorioTotale() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $irregolare = $campione->getSpesaIrregolarePreContraddittorio();
            
            if (!is_null($irregolare)) {
                $importo += $irregolare;
            }
        }
        
        return $importo + $this->spesa_irregolare_pre_contraddittorio;
    }
	/* FINE FORMULE SPESA IRREGOLARE PRE CONTRADDITTORIO*/
    
	/* INIZIO FORMULE SPESA IRREGOLARE POST CONTRADDITTORIO*/
    public function calcolaIrregolarePostContraddittorioCampioni() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $irregolare = $campione->getSpesaIrregolarePostContraddittorio();
            
            if (!is_null($irregolare)) {
                $importo += $irregolare;
            }
        }
        
        return $importo;        
    }
    
	public function calcolaIrregolarePostContraddittorioTotale() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $irregolare = $campione->getSpesaIrregolarePostContraddittorio();
            
            if (!is_null($irregolare)) {
                $importo += $irregolare;
            }
        }
        
        return $importo + $this->spesa_irregolare_post_contraddittorio;        
    }
	/* FINE FORMULE SPESA IRREGOLARE POST CONTRADDITTORIO*/
    
	/*INIZIO FORMULE TAGLIO ADA PROPOSTO */
    public function calcolaTaglioAdaCampioni() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $taglio = $campione->getTaglioProposto();
            
            if (!is_null($taglio)) {
                $importo += $taglio;
            }
        }
        
        return $importo;        
    }  
	
	public function calcolaTaglioAdaTotale() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $taglio = $campione->getTaglioProposto();
            
            if (!is_null($taglio)) {
                $importo += $taglio;
            }
        }
        
        return $importo + $this->taglio_ada_proposto;        
    }
	/* FINE FORMULE TAGLIO ADA PROPOSTO */
    
    public function calcolaDeltaControllato() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $importo_gius = $campione->getGiustificativo()->getImportoApprovato();
            
            if (!is_null($importo_gius)) {
                $importo += $importo_gius;
            }
        }
        
        return $importo;          
    }
    
    public function calcolaPercentualeGiustificativiControllati() {
        $numeroGiustificativi = 0;
        $numeroCampioni = count($this->getCampioni());
        
        foreach ($this->richiesta->getAttuazioneControllo()->getPagamenti() as $pagamento) {
            // if ($pagamento->getEsitoIstruttoria()) {
                $numeroGiustificativi += count($pagamento->getGiustificativi());
            // }
        }
        
        if ($numeroGiustificativi == 0) {
            return 0;
        } else {
            return round($numeroCampioni / $numeroGiustificativi * 100, 2);
        }
    }
	
	public function getContributoIrregolarePreContraddittorio() {
		return $this->contributo_irregolare_pre_contraddittorio;
	}

	public function getContributoIrregolarePostContraddittorio() {
		return $this->contributo_irregolare_post_contraddittorio;
	}

	public function setContributoIrregolarePreContraddittorio($contributo_irregolare_pre_contraddittorio) {
		$this->contributo_irregolare_pre_contraddittorio = $contributo_irregolare_pre_contraddittorio;
	}

	public function setContributoIrregolarePostContraddittorio($contributo_irregolare_post_contraddittorio) {
		$this->contributo_irregolare_post_contraddittorio = $contributo_irregolare_post_contraddittorio;
	}
	
	public function getSpesaCuscinetto() {
		return $this->spesa_cuscinetto;
	}

	public function getContributoPubblicoCuscinetto() {
		return $this->contributo_pubblico_cuscinetto;
	}

	public function setSpesaCuscinetto($spesa_cuscinetto) {
		$this->spesa_cuscinetto = $spesa_cuscinetto;
	}

	public function setContributoPubblicoCuscinetto($contributo_pubblico_cuscinetto) {
		$this->contributo_pubblico_cuscinetto = $contributo_pubblico_cuscinetto;
	}
	
	/* INIZIO FORMULE CONTIRBUTO IRREGOLARE PRE CONTRADDITTORIO*/
    public function calcolaContributoPreContraddittorioCampioni() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $irregolare = $campione->getContributoPubblicoPreContraddittorio();
            
            if (!is_null($irregolare)) {
                $importo += $irregolare;
            }
        }
        
        return $importo;        
    }
    
	public function calcolaContributoPreContraddittorioTotale() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $irregolare = $campione->getContributoPubblicoPreContraddittorio();
            
            if (!is_null($irregolare)) {
                $importo += $irregolare;
            }
        }
        
        return $importo + $this->contributo_irregolare_pre_contraddittorio;        
    }
	/* FINE FORMULE CONTIRBUTO IRREGOLARE PRE CONTRADDITTORIO*/
	
	/* INIZIO FORMULE CONTIRBUTO IRREGOLARE POST CONTRADDITTORIO*/
    public function calcolaContributoPostContraddittorioCampioni() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $irregolare = $campione->getContributoPubblicoPostContraddittorio();
            
            if (!is_null($irregolare)) {
                $importo += $irregolare;
            }
        }
        
        return $importo;        
    }
    
	public function calcolaContributoPostContraddittorioTotale() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $irregolare = $campione->getContributoPubblicoPostContraddittorio();
            
            if (!is_null($irregolare)) {
                $importo += $irregolare;
            }
        }
        
        return $importo + $this->contributo_irregolare_post_contraddittorio;        
    }
	/* FINE FORMULE CONTIRBUTO IRREGOLARE POST CONTRADDITTORIO*/
	
	/* INIZIO FORMULE CONTIRBUTO SOTTOPOSTA A AUDIT*/
    public function calcolaSottopostaAuditCampioni() {
        $importo = 0;
        foreach ($this->getCampioni() as $campione) {          
            $temp = $campione->getGiustificativo()->getTotaleImputatoApprovato();
            
            if (!is_null($temp)) {
                $importo += $temp;
            }
        }
        
        return $importo;        
    }
    
	/* FINE FORMULE CONTIRBUTO SOTTOPOSTA A AUDIT*/
   
}
