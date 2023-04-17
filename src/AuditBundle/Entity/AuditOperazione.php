<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Table(name="audit_operazioni")
 * @ORM\Entity()
 */
class AuditOperazione extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Audit", inversedBy="audit_operazione")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $audit;

	/**
	 * @ORM\Column(name="nome", type="string", length=200, nullable=true)
	 */
	protected $nome;

	/**
	 * @var boolean $rating
	 * @ORM\Column(type="boolean", name="campione_stratificato", nullable=true)
	 */
	protected $campione_stratificato;

	/**
	 * @ORM\ManyToOne(targetEntity="MetodologiaCampionamento")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $modalita_campionamento;

	/**
	 * @ORM\ManyToOne(targetEntity="TipoCampione")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $tipo_campione;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $numero_operazioni_campione;

	/**
	 * @ORM\Column(name="spesa_certificata", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $spesa_certificata;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $numero_strati_campione;

	/**
	 * @ORM\Column(name="passo_campionamento", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $passo_campionamento;

	/**
	 * @ORM\Column(name="spesa_certificata_strato", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $spesa_certificata_strato;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $numero_operazioni_universo;

	/**
	 * @ORM\Column(name="devizione_standard", type="decimal", precision=14, scale=4, nullable=true)
	 */
	protected $devizione_standard;

	/**
	 * @ORM\Column(name="soglia_rilevanza", type="decimal", precision=14, scale=4, nullable=true)
	 */
	protected $soglia_rilevanza;

	/**
	 * @ORM\Column(name="periodo_da", type="date", nullable=true)
	 */
	protected $periodo_da;

	/**
	 * @ORM\Column(name="periodo_a", type="date", nullable=true)
	 */
	protected $periodo_a;

	/**
	 * @ORM\OneToMany(targetEntity="DocumentoOperazione", mappedBy="audit_operazione")
	 */
	protected $documenti_operazione;
	protected $documento;

	/**
	 * @ORM\OneToMany(targetEntity="AuditCampioneOperazione", mappedBy="audit_operazione", cascade={"persist"})
	 */
	protected $campioni;
	protected $campioni_estesi;

	function __construct() {
		$this->campioni = new \Doctrine\Common\Collections\ArrayCollection();
	}
	
	public function getId() {
		return $this->id;
	}

	public function getAudit() {
		return $this->audit;
	}

	public function getNome() {
		return $this->nome;
	}

	public function getCampioneStratificato() {
		return $this->campione_stratificato;
	}

	public function getModalitaCampionamento() {
		return $this->modalita_campionamento;
	}

	public function getTipoCampione() {
		return $this->tipo_campione;
	}

	public function getNumeroOperazioniCampione() {
		return $this->numero_operazioni_campione;
	}

	public function getSpesaCertificata() {
		return $this->spesa_certificata;
	}

	public function getNumeroStratiCampione() {
		return $this->numero_strati_campione;
	}

	public function getPassoCampionamento() {
		return $this->passo_campionamento;
	}

	public function getSpesaCertificataStrato() {
		return $this->spesa_certificata_strato;
	}

	public function getNumeroOperazioniUniverso() {
		return $this->numero_operazioni_universo;
	}

	public function getDevizioneStandard() {
		return $this->devizione_standard;
	}

	public function getSogliaRilevanza() {
		return $this->soglia_rilevanza;
	}

	public function getPeriodoDa() {
		return $this->periodo_da;
	}

	public function getPeriodoA() {
		return $this->periodo_a;
	}

	public function getDocumentiOperazione() {
		return $this->documenti_operazione;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setAudit($audit) {
		$this->audit = $audit;
	}

	public function setNome($nome) {
		$this->nome = $nome;
	}

	public function setCampioneStratificato($campione_stratificato) {
		$this->campione_stratificato = $campione_stratificato;
	}

	public function setModalitaCampionamento($modalita_campionamento) {
		$this->modalita_campionamento = $modalita_campionamento;
	}

	public function setTipoCampione($tipo_campione) {
		$this->tipo_campione = $tipo_campione;
	}

	public function setNumeroOperazioniCampione($numero_operazioni_campione) {
		$this->numero_operazioni_campione = $numero_operazioni_campione;
	}

	public function setSpesaCertificata($spesa_certificata) {
		$this->spesa_certificata = $spesa_certificata;
	}

	public function setNumeroStratiCampione($numero_strati_campione) {
		$this->numero_strati_campione = $numero_strati_campione;
	}

	public function setPassoCampionamento($passo_campionamento) {
		$this->passo_campionamento = $passo_campionamento;
	}

	public function setSpesaCertificataStrato($spesa_certificata_strato) {
		$this->spesa_certificata_strato = $spesa_certificata_strato;
	}

	public function setNumeroOperazioniUniverso($numero_operazioni_universo) {
		$this->numero_operazioni_universo = $numero_operazioni_universo;
	}

	public function setDevizioneStandard($devizione_standard) {
		$this->devizione_standard = $devizione_standard;
	}

	public function setSogliaRilevanza($soglia_rilevanza) {
		$this->soglia_rilevanza = $soglia_rilevanza;
	}

	public function setPeriodoDa($periodo_da) {
		$this->periodo_da = $periodo_da;
	}

	public function setPeriodoA($periodo_a) {
		$this->periodo_a = $periodo_a;
	}

	public function setDocumentiOperazione($documenti_operazione) {
		$this->documenti_operazione = $documenti_operazione;
	}

	public function getDocumento() {
		return $this->documento;
	}

	public function setDocumento($documento) {
		$this->documento = $documento;
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
		$campione->setAuditOperazione($this);;
	}

	public function addCampione($campione) {
		$this->campioni->add($campione);
		$campione->setAuditOperazione($this);
	}

}
