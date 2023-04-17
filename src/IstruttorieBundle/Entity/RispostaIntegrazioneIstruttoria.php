<?php

namespace IstruttorieBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\RichiestaCupBatch;
use BaseBundle\Annotation as Sfinge;

/**
 * RispostaIntegrazione
 *
 * @ORM\Table(name="risposte_integrazioni")
 * @ORM\Entity()
 */
class RispostaIntegrazioneIstruttoria extends EntityLoggabileCancellabile {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\OneToOne(targetEntity="IstruttorieBundle\Entity\IntegrazioneIstruttoria", inversedBy="risposta")
	 * @ORM\JoinColumn(name="integrazione_id", referencedColumnName="id")
	 */
	protected $integrazione;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $testo;

	/**
	 * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaIntegrazione", mappedBy="risposta_integrazione", cascade={"persist"})
	 */
	protected $richieste_protocollo;

	/**
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\DocumentoIntegrazioneIstruttoria", mappedBy="risposta_integrazione", cascade={"persist"})
	 */
	protected $documenti;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $documento_risposta;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $documento_risposta_firmato;

	/**
	 * @ORM\ManyToOne(targetEntity="AnagraficheBundle\Entity\Persona")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $firmatario;

	/**
	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\StatoIntegrazione")
	 * @ORM\JoinColumn(nullable=true)
	 * @Sfinge\CampoStato()
	 */
	private $stato;

	/**
	 * @ORM\Column(type="boolean", options={"default" : false}, name="presa_visione")
	 * @var bool $presa_visione
	 */
	private $presa_visione;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true, name="utente_presa_visione")
	 * @var string|null $utente_presa_visione
	 */
	private $utente_presa_visione;

	/**
	 * @ORM\Column(type="datetime", nullable=true, name="data_presa_visione")
	 * @var DateTime|null $data_presa_visione
	 */
	private $data_presa_visione;


	function __construct() {
		$this->richieste_protocollo = new \Doctrine\Common\Collections\ArrayCollection();
		$this->documenti = new \Doctrine\Common\Collections\ArrayCollection();
	}

	function getId() {
		return $this->id;
	}

	function getIntegrazione() {
		return $this->integrazione;
	}

	function getData() {
		return $this->data;
	}

	function getTesto() {
		return $this->testo;
	}

	function getRichiesteProtocollo() {
		return $this->richieste_protocollo;
	}

	function getDocumenti() {
		return $this->documenti;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setIntegrazione($integrazione) {
		$this->integrazione = $integrazione;
	}

	function setData($data) {
		$this->data = $data;
	}

	function setTesto($testo) {
		$this->testo = $testo;
	}

	function setRichiesteProtocollo($richieste_protocollo) {
		$this->richieste_protocollo = $richieste_protocollo;
	}

	function setDocumenti($documenti) {
		$this->documenti = $documenti;
	}

	public function getDocumentoRisposta() {
		return $this->documento_risposta;
	}

	public function setDocumentoRisposta($documento_risposta) {
		$this->documento_risposta = $documento_risposta;
	}

	public function getDocumentoRispostaFirmato() {
        if (!$this->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi() && empty($this->documento_risposta_firmato)) {
            return $this->documento_risposta;
        }
		return $this->documento_risposta_firmato;
	}

	public function setDocumentoRispostaFirmato($documento_risposta_firmato) {
		$this->documento_risposta_firmato = $documento_risposta_firmato;
	}

	function getFirmatario() {
		return $this->firmatario;
	}

	function setFirmatario($firmatario) {
		$this->firmatario = $firmatario;
	}

	public function getRichiesta() {
		return $this->getIntegrazione()->getIstruttoria()->getRichiesta();
	}

	public function getProcedura() {
		return $this->getRichiesta()->getProcedura();
	}

	public function getSoggetto() {
		return $this->getRichiesta()->getSoggetto();
	}

	public function getStato() {
		return $this->stato;
	}

	public function setStato($stato) {
		$this->stato = $stato;
	}

	public function getNomeClasse() {
		return "RispostaIntegrazioneIstruttoria";
	}

	/**
	 * @return bool
	 */
	public function isPresaVisione(): bool
	{
		return $this->presa_visione;
	}

	/**
	 * @param bool $presa_visione
	 */
	public function setPresaVisione(bool $presa_visione): void
	{
		$this->presa_visione = $presa_visione;
	}

	/**
	 * @return string|null
	 */
	public function getUtentePresaVisione(): ?string
	{
		return $this->utente_presa_visione;
	}

	/**
	 * @param string|null $utente_presa_visione
	 */
	public function setUtentePresaVisione(?string $utente_presa_visione): void
	{
		$this->utente_presa_visione = $utente_presa_visione;
	}

	/**
	 * @return DateTime|null
	 */
	public function getDataPresaVisione(): ?DateTime
	{
		return $this->data_presa_visione;
	}

	/**
	 * @param DateTime|null $data_presa_visione
	 */
	public function setDataPresaVisione(?DateTime $data_presa_visione): void
	{
		$this->data_presa_visione = $data_presa_visione;
	}

	/*
	 * Di solito l'integrazione dovrebbe avere una sola richiesta protocollo associata(al più una per tipo), ma può capitare che ne venga generata più di una
	 * ..per cui va presa sempre la richiesta protocollo più recente
	 * 
	 * questo metodo deve fare riferimento solo alle RichiesteProtocolloRispostaIntegrazione
	 * 
	 * attenzione: il filtraggio per nomeClasse risulta necessario perchè essendoci eredità in cascata anche se la relazione richieste_protocollo punta ad un tipo specifico
	 * può tornare anche istanze derivate dalla classe specificata
	 */
	public function getProtocolloRispostaIntegrazione() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach($this->richieste_protocollo as $r){
			if($r->getNomeClasse() == 'RichiestaProtocolloRispostaIntegrazione'){
				$richiestaProtocollo = $r;				
			}
		}
		
		$protocollo = '-';
		if (!is_null($richiestaProtocollo)) {
			$protocollo = $richiestaProtocollo->getProtocollo();
		}
		
		return $protocollo;
	}
	
	public function getDataProtocolloRispostaIntegrazione() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach($this->richieste_protocollo as $r){
			if($r->getNomeClasse() == 'RichiestaProtocolloRispostaIntegrazione'){
				$richiestaProtocollo = $r;				
			}
		}
		
		$data = null;
		if (!is_null($richiestaProtocollo)) {
			$data = $richiestaProtocollo->getDataPg();
		}
		
		return $data;
	}
}
