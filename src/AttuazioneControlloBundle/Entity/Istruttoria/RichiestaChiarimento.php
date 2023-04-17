<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Annotation as Sfinge;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimentoRepository")
 * @ORM\Table(name="richieste_chiarimenti")
 */
class RichiestaChiarimento extends \BaseBundle\Entity\EntityLoggabileCancellabile{

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="richieste_chiarimenti")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $pagamento;

	/**
	 * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloRichiestaChiarimenti", mappedBy="richiesta_chiarimenti", cascade={"persist"})
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
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimentiDocumento", mappedBy="richiesta_chiarimenti", cascade={"persist"})
	 */
	protected $tipologie_documenti;

	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\RispostaRichiestaChiarimenti", mappedBy="richieste_chiarimenti", cascade={"persist"})
	 */
	protected $risposta;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $documento;

	/**
	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\StatoRichiestaChiarimenti")
	 * @ORM\JoinColumn(nullable=true)
	 * @Sfinge\CampoStato()
	 */
	private $stato;

	/**
	 * @ORM\Column(type="text", nullable=false)
	 */
	protected $testoEmail;
	
	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $istruttoria_oggetto_pagamento;

	/**
	 * @var Collection|AllegatoRichiestaChiarimento[]
	 * @ORM\OneToMany(targetEntity="AllegatoRichiestaChiarimento", mappedBy="richiesta_chiarimento", cascade={"persist"})
	 */
	protected $allegati;
	
	public function __construct() {
		$this->richieste_protocollo = new ArrayCollection();
		$this->tipologie_documenti = new ArrayCollection();
		$this->allegati = new ArrayCollection();
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

	public function getTestoEmail() {
		return $this->testoEmail;
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

	public function setTestoEmail($testoEmail) {
		$this->testoEmail = $testoEmail;
	}

	function addTipologiaDocumento($tipologie_documenti) {
		$this->tipologie_documenti->add($tipologie_documenti);
		$tipologie_documenti->setRichiestaChiarimento($this);
	}

	
	public function getProtocolloRichiestaChiarimenti() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach($this->richieste_protocollo as $r){
			if($r->getNomeClasse() == 'ProtocolloRichiestaChiarimenti'){
				$richiestaProtocollo = $r;				
			}
		}
		
		$protocollo = '-';
		if (!is_null($richiestaProtocollo)) {
			$protocollo = $richiestaProtocollo->getProtocollo();
		}
		
		return $protocollo;
	}
	
	public function getDataProtocolloRichiestaChiarimenti() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach($this->richieste_protocollo as $r){
			if($r->getNomeClasse() == 'ProtocolloRichiestaChiarimenti'){
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
		if($this->getStato() == 'RICH_CHIAR_INSERITA') {
			return "Inserita da inviare";
		}
		if($this->getStato() == 'RICH_CHIAR_INVIATA_PA' ) {
			return "Inviata in attesa di protocollazione";
		}
		if($this->getStato() == 'RICH_CHIAR_PROTOCOLLATA' && $this->isInAttesaRisposta()) {
			return "In attesa di risposta";
		}
		if($this->getStato() == 'RICH_CHIAR_PROTOCOLLATA' && !$this->isInAttesaRisposta()) {
			return "Risposta caricata dal beneficiario";
		}
	}
	
	public function isStatoGestibile() {
		if($this->getStato() == 'RICH_CHIAR_INSERITA') {
			return true;
		}
		if($this->getStato() == 'RICH_CHIAR_INVIATA_PA' ) {
			return false;
		}
		if($this->getStato() == 'RICH_CHIAR_PROTOCOLLATA' && $this->isInAttesaRisposta()) {
			return false;
		}
		if($this->getStato() == 'RICH_CHIAR_PROTOCOLLATA' && !$this->isInAttesaRisposta()) {
			return false;
		}
	}

	public function isInAttesaRisposta() {
		$finali = array('RICH_CHIAR_INVIATA_PA','RICH_CHIAR_PROTOCOLLATA');
		if(!is_null($this->risposta) && !in_array($this->risposta->getStato()->getCodice(), $finali)) {
			return true;
		}
		else {
			return false;
		}
	}
	
	public function getSoggetto() {
		return $this->pagamento->getSoggetto();
	}
	
	public function getIstruttoriaOggettoPagamento() {
		return $this->istruttoria_oggetto_pagamento;
	}

	public function setIstruttoriaOggettoPagamento($istruttoria_oggetto_pagamento) {
		$this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
	}
	
	public function getNomeClasse() {
		return "RichiestaChiarimento";
	}
	
	public function getRichiesta() {
		return $this->pagamento->getRichiesta();
	}
	
	public function getRichiestaChiarimento() {
		return $this;
	}
	
	/**
	 * @return Collection|AllegatoRichiestaChiarimento[]
	 */
	public function getAllegati(): Collection
	{
		return $this->allegati;
	}

	public function addAllegati(AllegatoRichiestaChiarimento $allegato): self{
		$this->allegati[] = $allegato;

		return $this;
	}

	public function removeAllegati(AllegatoRichiestaChiarimento $allegato): void{
		$this->allegati->removeElement($allegato);
	}

}
