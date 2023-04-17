<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Annotation as Sfinge;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 *
 * @ORM\Entity(repositoryClass="IstruttorieBundle\Entity\IntegrazioneIstruttoriaRepository")
 * @ORM\Table(name="integrazioni_istruttorie")
 */
class IntegrazioneIstruttoria extends EntityLoggabileCancellabile {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\IstruttoriaRichiesta", inversedBy="integrazioni")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $istruttoria;

	/**
	 * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\ValutazioneChecklistIstruttoria", inversedBy="integrazioni")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $valutazione_checklist;

	/**
	 * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloIntegrazione", mappedBy="integrazione", cascade={"persist"})
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $richieste_protocollo;

	/**
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $data;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $testo;

	/**
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\IntegrazioneIstruttoriaDocumento", mappedBy="integrazione", cascade={"persist"})
	 */
	protected $tipologie_documenti;

	/**
	 * @ORM\OneToOne(targetEntity="IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria", mappedBy="integrazione", cascade={"persist"})
     * @var RispostaIntegrazioneIstruttoria
	 */
	protected $risposta;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $documento;

	/**
	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\StatoIntegrazione")
	 * @ORM\JoinColumn(nullable=true)
	 * @Sfinge\CampoStato()
	 */
	private $stato;

	/**
	 * @ORM\Column(type="text", nullable=false)
	 */
	protected $testoEmail;
    
    protected $tipologie_documenti_estesi; 
	
	public function __construct() {
		$this->richieste_protocollo = new \Doctrine\Common\Collections\ArrayCollection();
		$this->tipologie_documenti = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getId() {
		return $this->id;
	}

	public function getIstruttoria(): ?IstruttoriaRichiesta {
		return $this->istruttoria;
	}

	public function getValutazioneChecklist() {
		return $this->valutazione_checklist;
	}

	public function getRichiesteProtocollo() {
		return $this->richieste_protocollo;
	}

	public function getData() {
		return $this->data;
	}

	public function getTesto() {
		return $this->testo;
	}

	public function getTipologieDocumenti() {
		return $this->tipologie_documenti;
	}

	public function getRisposta() {
		return $this->risposta;
	}

	public function getDocumento() {
		return $this->documento;
	}

	public function getStato() {
		return $this->stato;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setIstruttoria($istruttoria) {
		$this->istruttoria = $istruttoria;
	}

	public function setValutazioneChecklist($valutazione_checklist) {
		$this->valutazione_checklist = $valutazione_checklist;
	}

	public function setRichiesteProtocollo($richieste_protocollo) {
		$this->richieste_protocollo = $richieste_protocollo;
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function setTesto($testo) {
		$this->testo = $testo;
	}

	public function setTipologieDocumenti($tipologie_documenti) {
		$this->tipologie_documenti = $tipologie_documenti;
	}

	public function setRisposta($risposta) {
		$this->risposta = $risposta;
	}

	public function setDocumento($documento) {
		$this->documento = $documento;
	}

	public function setStato($stato) {
		$this->stato = $stato;
	}

	function addTipologiaDocumento($tipologie_documenti) {
		$this->tipologie_documenti->add($tipologie_documenti);
		$tipologie_documenti->setIntegrazione($this);
	}

	public function getRichiesta() {
		return $this->getIstruttoria()->getRichiesta();
	}

	public function getProcedura() {
		return $this->getIstruttoria()->getRichiesta()->getProcedura();
	}

	public function getNomeClasse() {
		return "IntegrazioneIstruttoria";
	}

	public function hasRispostaInviata() {
		if (is_null($this->getRisposta())) {
			return false;
		}

		return !is_null($this->getRisposta()->getData());
	}
	
	public function getSoggetto() {
		return $this->getIstruttoria()->getRichiesta()->getSoggetto();
	}
	
	public function isInAttesaRisposta() {
		$finali = array('INT_INVIATA_PA','INT_PROTOCOLLATA');
		if(!in_array($this->risposta->getStato()->getCodice(), $finali)) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function getTestoEmail() {
		return $this->testoEmail;
	}

	function setTestoEmail($testoEmail) {
		$this->testoEmail = $testoEmail;
	}
	
	/*
	 * Di solito l'integrazione dovrebbe avere una sola richiesta protocollo associata(al più una per tipo), ma può capitare che ne venga generata più di una
	 * ..per cui va presa sempre la richiesta protocollo più recente
	 * 
	 * questo metodo deve fare riferimento solo alle RichiesteProtocolloIntegrazione
	 * 
	 * attenzione: il filtraggio per nomeClasse risulta necessario perchè essendoci eredità in cascata anche se la relazione richieste_protocollo punta ad un tipo specifico
	 * può tornare anche istanze derivate dalla classe specificata
	 */
	public function getProtocolloIntegrazione() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach($this->richieste_protocollo as $r){
			if($r->getNomeClasse() == 'RichiestaProtocolloIntegrazione'){
				$richiestaProtocollo = $r;				
			}
		}
		
		$protocollo = '-';
		if (!is_null($richiestaProtocollo)) {
			$protocollo = $richiestaProtocollo->getProtocollo();
		}
		
		return $protocollo;
	}
	
	public function getDataProtocolloIntegrazione() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach($this->richieste_protocollo as $r){
			if($r->getNomeClasse() == 'RichiestaProtocolloIntegrazione'){
				$richiestaProtocollo = $r;				
			}
		}
		
		$data = null;
		if (!is_null($richiestaProtocollo)) {
			$data = $richiestaProtocollo->getDataPg();
		}
		
		return $data;
	}
    
    public function getTipologieDocumentiEstesi() {
        return $this->tipologie_documenti_estesi;
    }

    public function setTipologieDocumentiEstesi($tipologie_documenti_estesi) {
        $this->tipologie_documenti_estesi = $tipologie_documenti_estesi;
    }
    
    public function addTipologieDocumentiEstesi($tipologia) {
		$this->tipologie_documenti_estesi[] = $tipologia;
		$tipologia->setIntegrazione($this);
	}

    /**
     * L'integrazione di istruttoria è eliminabile
     * solamente se *non* protocollata.
     * @return bool
     */
	public function isEliminabile() {
	    $statiIntegrazione = ['INT_INSERITA'];
	    if (in_array($this->getStato()->getCodice(), $statiIntegrazione)) {
	        return true;
        } else {
            return false;
        }
    }
}
