<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Table(name="audit_campioni_giustificativi")
 * @ORM\Entity()
 */
class AuditCampioneGiustificativo {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AuditCampioneOperazione", inversedBy="campioni")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $audit_campione_operazione;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\GiustificativoPagamento")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $giustificativo;
    
	protected $selezionato;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $conforme;    
    
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $taglio_pre_contraddittorio;   
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $contributo_pubblico_pre_contraddittorio;   
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $contributo_pubblico_post_contraddittorio;   
    
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $taglio_post_contraddittorio;       
     
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
	protected $taglio_proposto;
    
	/**
	 * @ORM\Column(name="azione_correttiva", type="string", length=50, nullable=true)
	 */
	protected $azione_correttiva; 
		
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
    
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $note;   
     
 	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_termine_followup;    
	
	/**
	 * @ORM\OneToMany(targetEntity="DocumentoCampioneGiustificativo", mappedBy="audit_campione")
	 */
	protected $documenti_campione_giustificativo;
    
    public function getId() {
        return $this->id;
    }

    public function getAuditCampioneOperazione() {
        return $this->audit_campione_operazione;
    }

    public function getGiustificativo() {
        return $this->giustificativo;
    }

    public function getSelezionato() {
        return $this->selezionato;
    }

    public function getConforme() {
        return $this->conforme;
    }

    public function getTaglioPreContraddittorio() {
        return $this->taglio_pre_contraddittorio;
    }

    public function getTaglioPostContraddittorio() {
        return $this->taglio_post_contraddittorio;
    }

    public function getTaglioProposto() {
        return $this->taglio_proposto;
    }

    public function getAzioneCorrettiva() {
        return $this->azione_correttiva;
    }

    public function getNote() {
        return $this->note;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setAuditCampioneOperazione($audit_campione_operazione) {
        $this->audit_campione_operazione = $audit_campione_operazione;
    }

    public function setGiustificativo($giustificativo) {
        $this->giustificativo = $giustificativo;
    }

    public function setSelezionato($selezionato) {
        $this->selezionato = $selezionato;
    }

    public function setConforme($conforme) {
        $this->conforme = $conforme;
    }

    public function setTaglioPreContraddittorio($taglio_pre_contraddittorio) {
        $this->taglio_pre_contraddittorio = $taglio_pre_contraddittorio;
    }

    public function setTaglioPostContraddittorio($taglio_post_contraddittorio) {
        $this->taglio_post_contraddittorio = $taglio_post_contraddittorio;
    }

    public function setTaglioProposto($taglio_proposto) {
        $this->taglio_proposto = $taglio_proposto;
    }

    public function setAzioneCorrettiva($azione_correttiva) {
        $this->azione_correttiva = $azione_correttiva;
    }

    public function setNote($note) {
        $this->note = $note;
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
 
    public function getDataTermineFollowup() {
        return $this->data_termine_followup;
    }

    public function setDataTermineFollowup($data_termine_followup) {
        $this->data_termine_followup = $data_termine_followup;
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
	
	public function getContributoPubblicoPreContraddittorio() {
		return $this->contributo_pubblico_pre_contraddittorio;
	}

	public function setContributoPubblicoPreContraddittorio($contributo_pubblico_pre_contraddittorio) {
		$this->contributo_pubblico_pre_contraddittorio = $contributo_pubblico_pre_contraddittorio;
	}

	public function getDocumentiCampioneGiustificativo() {
		return $this->documenti_campione_giustificativo;
	}

	public function setDocumentiCampioneGiustificativo($documenti_campione_giustificativo) {
		$this->documenti_campione_giustificativo = $documenti_campione_giustificativo;
	}
	
	public function getContributoPubblicoPostContraddittorio() {
		return $this->contributo_pubblico_post_contraddittorio;
	}

	public function setContributoPubblicoPostContraddittorio($contributo_pubblico_post_contraddittorio) {
		$this->contributo_pubblico_post_contraddittorio = $contributo_pubblico_post_contraddittorio;
	}

}
