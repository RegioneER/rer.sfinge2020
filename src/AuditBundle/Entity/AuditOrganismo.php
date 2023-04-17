<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Table(name="audit_istanze_organismi")
 * @ORM\Entity()
 */
class AuditOrganismo extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Audit", inversedBy="audit_organismo")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $audit;
	
	/**
	 * @ORM\OneToMany(targetEntity="AuditRequisito", mappedBy="audit_organismo", cascade={"persist"})
	 */
	protected $audit_requisiti;

	/**
	 * @ORM\ManyToOne(targetEntity="Organismo")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $organismo;

	/**
	 * @ORM\Column(name="data_controllo", type="datetime", nullable=true)
	 */
	protected $data_controllo;

	/**
	 * @ORM\Column(name="luogo_controllo", type="string", length=200, nullable=true)
	 */
	protected $luogo_controllo;

	/**
	 * @ORM\OneToMany(targetEntity="DocumentoPianificazioneOrganismo", mappedBy="audit_organismo")
	 */
	protected $documenti_pianificazione_organismo;
	
	protected $documento;
	
	protected $audit_requisiti_estesi;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $valutazione;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_relazione; 
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $note;   
   
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $osservazioni_rac;
    
	/**
	 * @ORM\OneToMany(targetEntity="DocumentoAttuazioneOrganismo", mappedBy="audit_organismo")
	 */
	protected $documenti_attuazione_organismo;    

	public function getId() {
		return $this->id;
	}

	public function getAudit() {
		return $this->audit;
	}

	public function getOrganismo() {
		return $this->organismo;
	}

	public function getDataControllo() {
		return $this->data_controllo;
	}

	public function getLuogoControllo() {
		return $this->luogo_controllo;
	}

	public function getDocumentiPianificazioneOrganismo() {
		return $this->documenti_pianificazione_organismo;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setAudit($audit) {
		$this->audit = $audit;
	}

	public function setOrganismo($organismo) {
		$this->organismo = $organismo;
	}

	public function setDataControllo($data_controllo) {
		$this->data_controllo = $data_controllo;
	}

	public function setLuogoControllo($luogo_controllo) {
		$this->luogo_controllo = $luogo_controllo;
	}

	public function setDocumentiPianificazioneOrganismo($documenti_pianificazione_organismo) {
		$this->documenti_pianificazione_organismo = $documenti_pianificazione_organismo;
	}
	
	public function getDocumento() {
		return $this->documento;
	}

	public function setDocumento($documento) {
		$this->documento = $documento;
	}

	public function getAuditRequisiti() {
		return $this->audit_requisiti;
	}

	public function setAuditRequisiti($audit_requisiti) {
		$this->audit_requisiti = $audit_requisiti;
	}
	
	public function addAuditRequisitoEsteso($audit_requisito_esteso) {
        $this->audit_requisiti_estesi[] = $audit_requisito_esteso;
        $audit_requisito_esteso->setAuditOrganismo($this);
    }
    
    public function addAuditRequisito($audit_requisito) {
        $this->audit_requisiti->add($audit_requisito);
        $audit_requisito->setAuditOrganismo($this);
    }
	
	public function getAuditRequisitiEstesi() {
		return $this->audit_requisiti_estesi;
	}

	public function setAuditRequisitiEstesi($audit_requisiti_estesi) {
		$this->audit_requisiti_estesi = $audit_requisiti_estesi;
	}

    public function getValutazione() {
        return $this->valutazione;
    }

    public function getDataRelazione() {
        return $this->data_relazione;
    }

    public function getNote() {
        return $this->note;
    }

    public function getOsservazioniRac() {
        return $this->osservazioni_rac;
    }

    public function setValutazione($valutazione) {
        $this->valutazione = $valutazione;
    }

    public function setDataRelazione($data_relazione) {
        $this->data_relazione = $data_relazione;
    }

    public function setNote($note) {
        $this->note = $note;
    }

    public function setOsservazioniRac($osservazioni_rac) {
        $this->osservazioni_rac = $osservazioni_rac;
    }
    
    public function getDocumentiAttuazioneOrganismo() {
        return $this->documenti_attuazione_organismo;
    }

    public function setDocumentiAttuazioneOrganismo($documenti_attuazione_organismo) {
        $this->documenti_attuazione_organismo = $documenti_attuazione_organismo;
    }

}
