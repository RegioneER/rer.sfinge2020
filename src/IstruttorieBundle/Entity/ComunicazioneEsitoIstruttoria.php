<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Annotation as Sfinge;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 *
 * @ORM\Entity(repositoryClass="IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoriaRepository")
 * @ORM\Table(name="comunicazioni_esiti_istruttorie")
 */
class ComunicazioneEsitoIstruttoria extends EntityLoggabileCancellabile {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\IstruttoriaRichiesta", inversedBy="comunicazioni_esiti")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $istruttoria;

	/**
	 * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloEsitoIstruttoria", mappedBy="comunicazione_esito", cascade={"persist"})
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
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoriaDocumento", mappedBy="comunicazione", cascade={"persist"})
	 */
	protected $documenti_comunicazione;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $documento;

	/**
	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\StatoComunicazioneEsitoIstruttoria")
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
	 * @ORM\OneToOne(targetEntity="IstruttorieBundle\Entity\RispostaComunicazioneEsitoIstruttoria", mappedBy="comunicazione", cascade={"persist"})
	 */
	protected $risposta;

	public function __construct() {
		$this->richieste_protocollo = new \Doctrine\Common\Collections\ArrayCollection();
		$this->documenti_comunicazione = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getId() {
		return $this->id;
	}

	public function getIstruttoria() {
		return $this->istruttoria;
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

	public function setIstruttoria($istruttoria) {
		$this->istruttoria = $istruttoria;
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

	public function getRichiesta() {
		return $this->getIstruttoria()->getRichiesta();
	}

	public function getProcedura() {
		return $this->getIstruttoria()->getRichiesta()->getProcedura();
	}

	public function getNomeClasse() {
		return "ComunicazioneEsitoIstruttoria";
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
		if ($this->rispondibile == true && is_null($this->risposta)) {
			return true;
		}
		if ($this->rispondibile == true && !is_null($this->risposta)) {
			if ($this->risposta->getStato()->getCodice() != 'ESI_INVIATA_PA' && $this->risposta->getStato()->getCodice() != 'ESI_PROTOCOLLATA')
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

	/*
	 * Di solito l'integrazione dovrebbe avere una sola richiesta protocollo associata(al più una per tipo), ma può capitare che ne venga generata più di una
	 * ..per cui va presa sempre la richiesta protocollo più recente
	 * 
	 * questo metodo deve fare riferimento solo alle RichiesteProtocolloIntegrazione
	 * 
	 * attenzione: il filtraggio per nomeClasse risulta necessario perchè essendoci eredità in cascata anche se la relazione richieste_protocollo punta ad un tipo specifico
	 * può tornare anche istanze derivate dalla classe specificata
	 */

	public function getProtocolloEsitoIstruttoria() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach ($this->richieste_protocollo as $r) {
			if ($r->getNomeClasse() == 'RichiestaProtocolloEsitoIstruttoria') {
				$richiestaProtocollo = $r;
			}
		}

		$protocollo = '-';
		if (!is_null($richiestaProtocollo)) {
			$protocollo = $richiestaProtocollo->getProtocollo();
		}

		return $protocollo;
	}

	public function getDataProtocolloEsitoIstruttoria() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach ($this->richieste_protocollo as $r) {
			if ($r->getNomeClasse() == 'RichiestaProtocolloEsitoIstruttoria') {
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
		if ($this->getStato() == 'ESI_INSERITA') {
			return "Inserita da inviare";
		}
		else if ($this->getStato() == 'ESI_INVIATA_PA') {
			return "Inviata in attesa di protocollazione";
		}
		else if ($this->getStato() == 'ESI_PROTOCOLLATA'  && $this->rispondibile == false) {
			return "Comunicazione inviata";
		}
		else if ($this->getStato() == 'ESI_PROTOCOLLATA'  && !$this->isInAttesaRisposta()) {
			return "Risposta inviata";
		}
		else if ($this->getStato() == 'ESI_PROTOCOLLATA'  && $this->isInAttesaRisposta()) {
			return "In attesa di risposta";
		}
		else {
			return "-";
		}
	}

}
