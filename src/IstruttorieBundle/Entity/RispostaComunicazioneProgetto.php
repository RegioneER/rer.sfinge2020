<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\RichiestaCupBatch;
use BaseBundle\Annotation as Sfinge;

/**
 * RispostaComunicazioneProgetto
 *
 * @ORM\Table(name="risposte_comunicazioni_progetto")
 * @ORM\Entity()
 */
class RispostaComunicazioneProgetto extends EntityLoggabileCancellabile {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\OneToOne(targetEntity="IstruttorieBundle\Entity\ComunicazioneProgetto", inversedBy="risposta")
	 * @ORM\JoinColumn(name="comunicazione_id", referencedColumnName="id")
	 */
	protected $comunicazione;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $testo;

	/**
	 * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaComunicazioneProgetto", mappedBy="risposta_comunicazione_progetto", cascade={"persist"})
	 */
	protected $richieste_protocollo;

	/**
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\RispostaComunicazioneProgettoDocumento", mappedBy="risposta_comunicazione", cascade={"persist"})
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
	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\StatoComunicazioneProgetto")
	 * @ORM\JoinColumn(nullable=true)
	 * @Sfinge\CampoStato()
	 */
	private $stato;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_invio;

	function __construct() {
		$this->richieste_protocollo = new \Doctrine\Common\Collections\ArrayCollection();
		$this->documenti = new \Doctrine\Common\Collections\ArrayCollection();
	}

	function getId() {
		return $this->id;
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
	
	public function getComunicazione() {
		return $this->comunicazione;
	}

	public function setComunicazione($comunicazione) {
		$this->comunicazione = $comunicazione;
	}

	public function getRichiesta() {
		return $this->getComunicazione()->getRichiesta();
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
	
	public function getDataInvio() {
		return $this->data_invio;
	}

	public function setDataInvio($data_invio) {
		$this->data_invio = $data_invio;
	}

	public function getNomeClasse() {
		return "RispostaComunicazioneProgetto";
	}
	
	
	public function getProtocolloRispostaComunicazione() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach($this->richieste_protocollo as $r){
			if($r->getNomeClasse() == 'RichiestaProtocolloRispostaComunicazioneProgetto'){
				$richiestaProtocollo = $r;				
			}
		}
		
		$protocollo = '-';
		if (!is_null($richiestaProtocollo)) {
			$protocollo = $richiestaProtocollo->getProtocollo();
		}
		
		return $protocollo;
	}
	
	public function getDataProtocolloRispostaComunicazione() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach($this->richieste_protocollo as $r){
			if($r->getNomeClasse() == 'RichiestaProtocolloRispostaComunicazioneProgetto'){
				$richiestaProtocollo = $r;				
			}
		}
		
		$data = null;
		if (!is_null($richiestaProtocollo)) {
			$data = $richiestaProtocollo->getDataPg();
		}
		
		return $data;
	}
	
		
	public function getRispostaComunicazioneProgetto() {
		return $this;
	}

}
