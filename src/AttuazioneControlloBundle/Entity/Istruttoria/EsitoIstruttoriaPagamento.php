<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Annotation as Sfinge;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="esiti_istruttoria_pagamento")
 */
class EsitoIstruttoriaPagamento extends \BaseBundle\Entity\EntityLoggabileCancellabile{

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="esiti_istruttoria_pagamento")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $pagamento;

	/**
	 * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloEsitoIstruttoriaPagamento", mappedBy="esito_istruttoria_pagamento", cascade={"persist"})
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $richieste_protocollo;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $testo;

	/**
	 * @ORM\OneToOne(targetEntity="\DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 * contiene il pdf generato
	 */
	private $documento;

	/**
	 * @ORM\Column(type="text", nullable=false)
	 */
	protected $testoEmail;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\DocumentoEsitoIstruttoria", mappedBy="esito_istruttoria_pagamento", cascade={"persist"})
	 * contiene eventuali documenti aggiuntivi a supporto dell'esito (di cui al momento non si sa un cazzo)
	 */
	protected $documenti_esito_istruttoria;

	/**
	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\StatoEsitoIstruttoriaPagamento")
	 * @ORM\JoinColumn(nullable=true)
	 * @Sfinge\CampoStato()
	 */
	private $stato;
	
	/**
	 * @ORM\Column(name="note_alla_liquidazione", type="text", nullable=true)
	 */
	protected $noteAllaLiquidazione;
	
	public function __construct() {
		$this->richieste_protocollo = new \Doctrine\Common\Collections\ArrayCollection();
		$this->documenti_esito_istruttoria = new \Doctrine\Common\Collections\ArrayCollection();		
	}


		
	/*
	 * Di solito  dovrebbe esserci una sola richiesta protocollo associata(al più una per tipo), ma può capitare che ne venga generata più di una
	 * ..per cui va presa sempre la richiesta protocollo più recente
	 * 
	 * questo metodo deve fare riferimento solo alle RichiesteProtocolloEsitoIstruttoriaPagamento
	 * 
	 * attenzione: il filtraggio per nomeClasse risulta necessario perchè essendoci eredità in cascata anche se la relazione richieste_protocollo punta ad un tipo specifico
	 * può tornare anche istanze derivate dalla classe specificata
	 */
	public function getProtocolloEsitoIstruttoria() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach($this->richieste_protocollo as $r){
			if($r->getNomeClasse() == 'ProtocolloEsitoIstruttoriaPagamento'){
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
		foreach($this->richieste_protocollo as $r){
			if($r->getNomeClasse() == 'ProtocolloEsitoIstruttoriaPagamento'){
				$richiestaProtocollo = $r;				
			}
		}
		
		$data = null;
		if (!is_null($richiestaProtocollo)) {
			$data = $richiestaProtocollo->getDataPg();
		}
		
		return $data ? date_format($data,"d/m/Y") : $data;
	}
	
	public function getNomeClasse() {
		return "EsitoIstruttoriaPagamento";
	}
	
	public function getId() {
		return $this->id;
	}

	public function getPagamento() {
		return $this->pagamento;
	}

	public function getRichiesteProtocollo() {
		return $this->richieste_protocollo;
	}

	public function getTesto() {
		return $this->testo;
	}

	public function getDocumento() {
		return $this->documento;
	}

	public function getTestoEmail() {
		return $this->testoEmail;
	}

	public function getDocumentiEsitoIstruttoria() {
		return $this->documenti_esito_istruttoria;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setPagamento($pagamento) {
		$this->pagamento = $pagamento;
	}

	public function setRichiesteProtocollo($richieste_protocollo) {
		$this->richieste_protocollo = $richieste_protocollo;
	}

	public function setTesto($testo) {
		$this->testo = $testo;
	}

	public function setDocumento($documento) {
		$this->documento = $documento;
	}

	public function setTestoEmail($testoEmail) {
		$this->testoEmail = $testoEmail;
	}

	public function setDocumentiEsitoIstruttoria($documenti_esito_istruttoria) {
		$this->documenti_esito_istruttoria = $documenti_esito_istruttoria;
	}
	
	function getStato() {
		return $this->stato;
	}

	function setStato($stato) {
		$this->stato = $stato;
	}

	public function getRichiesta() {
		return $this->pagamento->getRichiesta();
	}	
	
	public function getSoggetto() {
		return $this->pagamento->getSoggetto();
	}
	
	public function addDocumentoEsitoIstruttoria($documento_esito_istruttoria) {
		$this->documenti_esito_istruttoria->add($documento_esito_istruttoria);
		$documento_esito_istruttoria->setEsitoIstruttoriaPagamento($this); 
	}
	
	public function isInviato(){
		return $this->stato->getCodice() == \BaseBundle\Entity\StatoEsitoIstruttoriaPagamento::ESITO_IP_INVIATA_PA ||
				$this->stato->getCodice() == \BaseBundle\Entity\StatoEsitoIstruttoriaPagamento::ESITO_IP_PROTOCOLLATA;
	}
	
	public function getNoteAllaLiquidazione() {
		return $this->noteAllaLiquidazione;
	}

	public function setNoteAllaLiquidazione($noteAllaLiquidazione) {
		$this->noteAllaLiquidazione = $noteAllaLiquidazione;
	}

}
