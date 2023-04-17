<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Annotation as Sfinge;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 *
 * @ORM\Entity(repositoryClass="IstruttorieBundle\Entity\ComunicazioneProgettoRepository")
 * @ORM\Table(name="comunicazioni_progetto")
 */
class ComunicazioneProgetto extends EntityLoggabileCancellabile {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="comunicazioni_progetto")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $richiesta;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\VariazioneRichiesta", inversedBy="comunicazioni_progetto")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $variazione;

	/**
	 * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloComunicazioneProgetto", mappedBy="comunicazione_progetto", cascade={"persist"})
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $richieste_protocollo;

	/**
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $data;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_invio;

	/**
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\ComunicazioneProgettoDocumento", mappedBy="comunicazione", cascade={"persist"})
	 */
	protected $documenti_comunicazione;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $documento;

	/**
	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\StatoComunicazioneProgetto")
	 * @ORM\JoinColumn(nullable=true)
	 * @Sfinge\CampoStato()
	 */
	private $stato;

	/**
	 * @ORM\Column(type="text", nullable=false)
	 */
	protected $testoEmail;

	/**
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $rispondibile;

	/**
	 * @ORM\OneToOne(targetEntity="IstruttorieBundle\Entity\RispostaComunicazioneProgetto", mappedBy="comunicazione", cascade={"persist"})
	 */
	protected $risposta;
	
	/**
	 * @ORM\Column(name="tipo_oggetto", type="text", nullable=true)
	 */
	protected $tipo_oggetto;

	public function __construct() {
		$this->richieste_protocollo = new \Doctrine\Common\Collections\ArrayCollection();
		$this->documenti_comunicazione = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getId() {
		return $this->id;
	}

	public function getRichiesteProtocollo() {
		return $this->richieste_protocollo;
	}

	public function getData() {
		return $this->data;
	}

	public function getDocumentiComunicazione() {
		return $this->documenti_comunicazione;
	}

	public function setDocumentiComunicazione($documenti_comunicazione) {
		$this->documenti_comunicazione = $documenti_comunicazione;
	}

	public function getDocumento() {
		return $this->documento;
	}

	public function getStato() {
		return $this->stato;
	}

	public function getTestoEmail() {
		return $this->testoEmail;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setRichiesteProtocollo($richieste_protocollo) {
		$this->richieste_protocollo = $richieste_protocollo;
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function setDocumento($documento) {
		$this->documento = $documento;
	}

	public function setStato($stato) {
		$this->stato = $stato;
	}

	public function setTestoEmail($testoEmail) {
		$this->testoEmail = $testoEmail;
	}

	function addDocumentiComunicazione($documenti_comunicazione) {
		$this->documenti_comunicazione->add($documenti_comunicazione);
		$documenti_comunicazione->setComunicazione($this);
	}

	public function getDataInvio() {
		return $this->data_invio;
	}

	public function setDataInvio($data_invio) {
		$this->data_invio = $data_invio;
	}

	public function getRispondibile() {
		return $this->rispondibile;
	}

	public function setRispondibile($rispondibile) {
		$this->rispondibile = $rispondibile;
	}

	public function getProcedura() {
		return $this->getRichiesta()->getProcedura();
	}

	public function getNomeClasse() {
		return "ComunicazioneProgetto";
	}

	public function hasRispostaInviata() {
		if (is_null($this->getRisposta())) {
			return false;
		}

		return !is_null($this->getRisposta()->getData());
	}

	public function getSoggetto() {
		if($this->tipo_oggetto == 'RICHIESTA') {
			return $this->getRichiesta()->getSoggetto();
		}
		if($this->tipo_oggetto == 'VARIAZIONE') {
			return $this->getVariazione()->getSoggetto();
		}
	}

	public function isInAttesaRisposta() {
		if ($this->rispondibile == true && is_null($this->risposta)) {
			return true;
		}
		if ($this->rispondibile == true && !is_null($this->risposta)) {
			if ($this->risposta->getStato()->getCodice() != 'COM_INVIATA_PA' && $this->risposta->getStato()->getCodice() != 'COM_PROTOCOLLATA')
				return true;
		}
		else {
			return false;
		}
	}

	public function getRisposta() {
		return $this->risposta;
	}

	public function setRisposta($risposta) {
		$this->risposta = $risposta;
	}

	public function getProtocolloComunicazione() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach ($this->richieste_protocollo as $r) {
			if ($r->getNomeClasse() == 'RichiestaProtocolloComunicazioneProgetto') {
				$richiestaProtocollo = $r;
			}
		}

		$protocollo = '-';
		if (!is_null($richiestaProtocollo)) {
			$protocollo = $richiestaProtocollo->getProtocollo();
		}

		return $protocollo;
	}

	public function getDataProtocolloComunicazione() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach ($this->richieste_protocollo as $r) {
			if ($r->getNomeClasse() == 'RichiestaProtocolloComunicazioneProgetto') {
				$richiestaProtocollo = $r;
			}
		}

		$data = null;
		if (!is_null($richiestaProtocollo)) {
			$data = $richiestaProtocollo->getDataPg();
		}

		return $data;
	}

	public function getStatoLeggibile() {
		if ($this->getStato() == 'COM_INSERITA') {
			return "Inserita da inviare";
		}
		else if ($this->getStato() == 'COM_INVIATA_PA') {
			return "Inviata in attesa di protocollazione";
		}
		else if ($this->getStato() == 'COM_PROTOCOLLATA'  && $this->rispondibile == false) {
			return "Comunicazione inviata";
		}
		else if ($this->getStato() == 'COM_PROTOCOLLATA'  && !$this->isInAttesaRisposta()) {
			return "Risposta inviata";
		}
		else if ($this->getStato() == 'COM_PROTOCOLLATA'  && $this->isInAttesaRisposta()) {
			return "In attesa di risposta";
		}
		else {
			return "-";
		}
	}
	
	public function getRichiesta() {
		if(!is_null($this->variazione)) {
			return $this->variazione->getRichiesta();
		}
		return $this->richiesta;
	}

	public function setRichiesta($richiesta) {
		$this->richiesta = $richiesta;
	}
	
	public function getTipoOggetto() {
		return $this->tipo_oggetto;
	}

	public function setTipoOggetto($tipo_oggetto) {
		$this->tipo_oggetto = $tipo_oggetto;
	}

	public function getVariazione() {
		return $this->variazione;
	}

	public function setVariazione($variazione) {
		$this->variazione = $variazione;
	}
	
	public function getComunicazioneProgetto() {
		return $this;
	}
}
