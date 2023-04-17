<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\RichiestaCupBatch;
use BaseBundle\Annotation as Sfinge;

/**
 * RispostaRichiestaChiarimenti
 *
 * @ORM\Table(name="risposte_richieste_chiarimenti")
 * @ORM\Entity()
 */
class RispostaRichiestaChiarimenti extends EntityLoggabileCancellabile {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento", inversedBy="risposta")
	 * @ORM\JoinColumn(name="richieste_chiarimenti_id", referencedColumnName="id")
	 */
	protected $richieste_chiarimenti;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $testo;

	/**
	 * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaRichiestaChiarimenti", mappedBy="risposta_richiesta_chiarimenti", cascade={"persist"})
	 */
	protected $richieste_protocollo;

	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaRichiestaChiarimenti", mappedBy="risposta_richiesta_chiarimenti", cascade={"persist"})
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
	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\StatoRichiestaChiarimenti")
	 * @ORM\JoinColumn(nullable=true)
	 * @Sfinge\CampoStato()
	 */
	private $stato;

	function __construct() {
		$this->richieste_protocollo = new \Doctrine\Common\Collections\ArrayCollection();
		$this->documenti = new \Doctrine\Common\Collections\ArrayCollection();
	}

	function getId() {
		return $this->id;
	}

	function getRichiestaChiarimenti() {
		return $this->richieste_chiarimenti;
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

	function setRichiestaChiarimenti($richieste_chiarimenti) {
		$this->richieste_chiarimenti = $richieste_chiarimenti;
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
		return $this->richieste_chiarimenti->getPagamento()->getRichiesta();
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
		return "RispostaRichiestaChiarimenti";
	}

	public function getProtocolloRispostaRichiestaChiarimenti() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach($this->richieste_protocollo as $r){
			if($r->getNomeClasse() == 'RichiestaProtocolloRispostaRichiestaChiarimenti'){
				$richiestaProtocollo = $r;				
			}
		}
		
		$protocollo = '-';
		if (!is_null($richiestaProtocollo)) {
			$protocollo = $richiestaProtocollo->getProtocollo();
		}
		
		return $protocollo;
	}
	
	public function getDataProtocolloRispostaRichiestaChiarimenti() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach($this->richieste_protocollo as $r){
			if($r->getNomeClasse() == 'RichiestaProtocolloRispostaRichiestaChiarimenti'){
				$richiestaProtocollo = $r;				
			}
		}
		
		$data = null;
		if (!is_null($richiestaProtocollo)) {
			$data = $richiestaProtocollo->getDataPg();
		}
		
		return $data;
	}
	
	public function getRispostaRichiestaChiarimenti() {
		return $this;
	}

}
