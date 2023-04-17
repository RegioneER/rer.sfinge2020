<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Table(name="audit_istanze_requisiti")
 * @ORM\Entity()
 */
class AuditRequisito extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AuditOrganismo", inversedBy="audit_requisiti")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $audit_organismo;

	/**
	 * @ORM\ManyToOne(targetEntity="Requisito", inversedBy="audit_requisiti")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $requisito;
    
	/**
	 * @ORM\OneToMany(targetEntity="AuditCampione", mappedBy="audit_requisito", cascade={"persist"})
	 */
	protected $campioni; 
   
    protected $campioni_estesi;      
  
	protected $selezionato;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $valutazione;  
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $note; 
    
	/**
	 * @ORM\OneToMany(targetEntity="DocumentoAttuazioneRequisito", mappedBy="audit_requisito")
	 */
	protected $documenti_attuazione_requisito;  
	
	protected $documento;

	public function getId() {
		return $this->id;
	}

	public function getAuditOrganismo() {
		return $this->audit_organismo;
	}

	public function getRequisito() {
		return $this->requisito;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setAuditOrganismo($audit_organismo) {
		$this->audit_organismo = $audit_organismo;
	}

	public function setRequisito($requisito) {
		$this->requisito = $requisito;
	}
    
    public function getCampioni() {
        return $this->campioni;
    }

    public function setCampioni($campioni) {
        $this->campioni = $campioni;
        return $this;
    }
    
    public function addCampioneEsteso($campione) {
        $this->campioni_estesi[] = $campione;
        $campione->setAuditRequisito($this);
    }
    
    public function addCampione($campione) {
        $this->campioni->add($campione);
        $campione->setAuditRequisito($this);
    }    
	
	public function getSelezionato() {
		return $this->selezionato;
	}

	public function setSelezionato($selezionato) {
		$this->selezionato = $selezionato;
	}
    
    public function getCampioniEstesi() {
        return $this->campioni_estesi;
    }

    public function setCampioniEstesi($campioni_estesi) {
        $this->campioni_estesi = $campioni_estesi;
        return $this;
    }
    
    public function getValutazione() {
        return $this->valutazione;
    }

    public function getNote() {
        return $this->note;
    }

    public function setValutazione($valutazione) {
        $this->valutazione = $valutazione;
    }

    public function setNote($note) {
        $this->note = $note;
    }
    
    public function getDocumentiAttuazioneRequisito() {
        return $this->documenti_attuazione_requisito;
    }

    public function setDocumentiAttuazioneRequisito($documenti_attuazione_requisito) {
        $this->documenti_attuazione_requisito = $documenti_attuazione_requisito;
    }

	public function setDocumento($documento) {
		$this->documento = $documento;
	}

	public function getDocumento() {
		return $this->documento;
	}

}
