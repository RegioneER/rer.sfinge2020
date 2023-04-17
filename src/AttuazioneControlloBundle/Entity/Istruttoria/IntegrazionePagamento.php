<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Annotation as Sfinge;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\ArrayCollection;
use AttuazioneControlloBundle\Entity\Pagamento;
use Doctrine\Common\Collections\Collection;
use ProtocollazioneBundle\Entity\RichiestaProtocolloIntegrazionePagamento;
use ProtocollazioneBundle\Entity\EmailProtocollo;
use DocumentoBundle\Entity\DocumentoFile;
use BaseBundle\Entity\StatoIntegrazione;
use AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento;
use RichiesteBundle\Entity\Richiesta;
use SoggettoBundle\Entity\Soggetto;

/**
 *
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamentoRepository")
 * @ORM\Table(name="integrazioni_pagamenti")
 */
class IntegrazionePagamento extends EntityLoggabileCancellabile {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="integrazioni")
	 * @ORM\JoinColumn(nullable=false)
	 * @var Pagamento|null
	 */
	protected $pagamento;

	/**
	 * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloIntegrazionePagamento", mappedBy="integrazione_pagamento", cascade={"persist"})
	 * @ORM\OrderBy({"id" = "ASC"})
	 * 
	 * @var Collection|\ProtocollazioneBundle\Entity\RichiestaProtocolloIntegrazionePagamento[]
	 */
	protected $richieste_protocollo;

	/**
	 * @ORM\Column(type="datetime", nullable=false)
	 * @var \DateTime|null
	 */
	protected $data;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @var string|null
	 */
	protected $testo;

	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamentoDocumento", mappedBy="integrazione", cascade={"persist"})
	 * @var Collection|IntegrazionePagamentoDocumento[]
	 */
	protected $tipologie_documenti;

	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\RispostaIntegrazionePagamento", mappedBy="integrazione", cascade={"persist"})
	 * @var RispostaIntegrazionePagamento|null
	 */
	protected $risposta;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 * @var DocumentoFile|null
	 */
	private $documento;

	/**
	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\StatoIntegrazione")
	 * @ORM\JoinColumn(nullable=true)
	 * @Sfinge\CampoStato()
	 * @var StatoIntegrazione|null
	 */
	private $stato;

	/**
	 * @ORM\Column(type="text", nullable=false)
	 * @var string|null
	 */
	protected $testoEmail;
	
	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 * @var IstruttoriaOggettoPagamento|null
	 */
	protected $istruttoria_oggetto_pagamento;

	/**
	 * @ORM\Column(name="giorni_per_risposta", type="smallint", nullable=true)
	 * @var int|null
	 */
	private $giorni_per_risposta;
	
	
	public function __construct() {
		$this->richieste_protocollo = new ArrayCollection();
		$this->tipologie_documenti = new ArrayCollection();
	}

	public function getId() {
		return $this->id;
	}

	public function getPagamento(): ?Pagamento {
		return $this->pagamento;
	}

	public function getRichiesteProtocollo(): Collection {
		return $this->richieste_protocollo;
	}

	public function getData(): ?\DateTime {
		return $this->data;
	}

	public function getTesto(): ?string {
		return $this->testo;
	}

	public function getTipologieDocumenti(): Collection {
		return $this->tipologie_documenti;
	}

	public function getRisposta(): ?RispostaIntegrazionePagamento {
		return $this->risposta;
	}

	public function getDocumento(): ?DocumentoFile {
		return $this->documento;
	}

	public function getStato(): ?StatoIntegrazione {
		return $this->stato;
	}

	public function getTestoEmail(): ?string {
		return $this->testoEmail;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setPagamento(?Pagamento $pagamento): self {
		$this->pagamento = $pagamento;

		return $this;
	}

	public function setRichiesteProtocollo(Collection $richieste_protocollo): self {
		$this->richieste_protocollo = $richieste_protocollo;

		return $this;
	}

	public function setData(\DateTime $data): self {
		$this->data = $data;
	
		return $this;
	}

	public function setTesto(?string $testo): self {
		$this->testo = $testo;
	
		return $this;
	}

	public function setTipologieDocumenti(Collection $tipologie_documenti): self {
		$this->tipologie_documenti = $tipologie_documenti;
	
		return $this;
	}

	public function setRisposta(?RispostaIntegrazionePagamento $risposta): self {
		$this->risposta = $risposta;
		
		return $this;
	}

	public function setDocumento(?DocumentoFile $documento): self {
		$this->documento = $documento;
	
		return $this;
	}

	public function setStato(?StatoIntegrazione $stato): self {
		$this->stato = $stato;
	
		return $this;
	}

	public function setTestoEmail(?string $testoEmail): self {
		$this->testoEmail = $testoEmail;
	
		return $this;
	}

	function addTipologiaDocumento(IntegrazionePagamentoDocumento $tipologie_documenti): self {
		$this->tipologie_documenti->add($tipologie_documenti);
		$tipologie_documenti->setIntegrazione($this);
	
		return $this;
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
			if($r->getNomeClasse() == 'ProtocolloIntegrazionePagamento'){
				$richiestaProtocollo = $r;				
			}
		}
		
		$protocollo = '-';
		if (!is_null($richiestaProtocollo)) {
			$protocollo = $richiestaProtocollo->getProtocollo();
		}
		
		return $protocollo;
	}
	
	public function getDataProtocolloIntegrazione(): ?\DateTime {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach($this->richieste_protocollo as $r){
			if($r->getNomeClasse() == 'ProtocolloIntegrazionePagamento'){
				$richiestaProtocollo = $r;				
			}
		}
		
		$data = null;
		if (!is_null($richiestaProtocollo)) {
			$data = $richiestaProtocollo->getDataPg();
		}
		
		return $data;
	}

	public function getStatoLeggibile(): ?string {
		if($this->getStato() == 'INT_INSERITA') {
			return "Inserita da inviare";
		}
		if($this->getStato() == 'INT_INVIATA_PA' ) {
			return "Inviata in attesa di protocollazione";
		}
		if($this->getStato() == 'INT_PROTOCOLLATA' && $this->isInAttesaRisposta()) {
			return "In attesa di risposta";
		}
		if($this->getStato() == 'INT_PROTOCOLLATA' && !$this->isInAttesaRisposta()) {
			return "Risposta caricata dal beneficiario";
		}

		return null;
	}
	
	public function isStatoGestibile(): ?bool {
		if($this->getStato() == 'INT_INSERITA') {
			return true;
		}
		if($this->getStato() == 'INT_INVIATA_PA' ) {
			return false;
		}
		if($this->getStato() == 'INT_PROTOCOLLATA' && $this->isInAttesaRisposta()) {
			return false;
		}
		if($this->getStato() == 'INT_PROTOCOLLATA' && !$this->isInAttesaRisposta()) {
			return false;
		}

		return null;
	}

	public function isInAttesaRisposta(): bool {
		$finali = array('INT_INVIATA_PA','INT_PROTOCOLLATA');
		if(!is_null($this->getRisposta()) && !in_array($this->getRisposta()->getStato()->getCodice(), $finali)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getSoggetto(): ?Soggetto {
		return $this->pagamento->getSoggetto();
	}
	
	public function getIstruttoriaOggettoPagamento(): ?IstruttoriaOggettoPagamento {
		return $this->istruttoria_oggetto_pagamento;
	}

	public function setIstruttoriaOggettoPagamento(?IstruttoriaOggettoPagamento $istruttoria_oggetto_pagamento) {
		$this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
	}

	/**
	 * @return int|null
	 */
	public function getGiorniPerRisposta(): ?int
	{
		return $this->giorni_per_risposta;
	}

	/**
	 * @param int|null $giorni_per_risposta
	 */
	public function setGiorniPerRisposta(?int $giorni_per_risposta): void
	{
		$this->giorni_per_risposta = $giorni_per_risposta;
	}
	
	public function getNomeClasse() {
		return "IntegrazionePagamento";
	}
	
	public function getRichiesta(): ?Richiesta {
		return $this->pagamento->getRichiesta();
	}
	
	public function getIntegrazione(): self {
		return $this;
	}

	public function getDataInvioPEC(): ?\DateTime{
		/** @var RichiestaProtocolloIntegrazionePagamento $protocollo*/
		$protocollo = $this->richieste_protocollo->last();
		if($protocollo){
			/** @var EmailProtocollo|bool $email */
			$email = $protocollo->getEmailProtocollo()->last();
			if($email){
				return $email->getDataInvio();
			}
		}

		return null;
	}
    
    public function calcolaGiorniTrascorsi() {
        $dataRisposta = new \DateTime();
        $dataPec = $this->getDataInvioPEC();
        if (!is_null($this->risposta)) {
            if (!is_null($this->risposta->getDataProtocolloRispostaIntegrazione())) {
                $dataRisposta = $this->risposta->getDataProtocolloRispostaIntegrazione();
            }
        }
        if (!is_null($dataPec)) {
            $intervallo = $dataPec->diff($dataRisposta);
        }
        else {
          $intervallo = $dataRisposta->diff($dataRisposta);  
        }
        return $intervallo->format('%d');
    }
    
    public function isScaduta() {
        return $this->pagamento->integrazioneScaduta();
    } 

}
