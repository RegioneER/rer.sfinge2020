<?php

namespace AttuazioneControlloBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use AttuazioneControlloBundle\Entity\Revoche\Revoca;
use Doctrine\Common\Collections\Collection;
use AttuazioneControlloBundle\Entity\VariazioneRichiesta;
use DocumentoBundle\Entity\DocumentoFile;
use AttuazioneControlloBundle\Entity\Proroga;
use AttuazioneControlloBundle\Entity\Pagamento;
use SfingeBundle\Entity\Procedura;

/**
 * AttuazioneControlloBundle
 *
 * @ORM\Table(name="attuazione_controllo_richieste")
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\AttuazioneControlloRichiestaRepository")
 */
class AttuazioneControlloRichiesta extends EntityLoggabileCancellabile {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\OneToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="attuazione_controllo")
	 * @ORM\JoinColumn(nullable=false)
	 * @var Richiesta
	 */
	protected $richiesta;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $contributo_accettato;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_limite_accettazione;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $documento_accettazione;

	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $utente_accettazione;

	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", mappedBy="attuazione_controllo_richiesta")
	 */
	protected $pagamenti;

	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Proroga", mappedBy="attuazione_controllo_richiesta")
	 */
	protected $proroghe;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_accettazione;

	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\VariazioneRichiesta", mappedBy="attuazione_controllo_richiesta")
	 * @var Collection|VariazioneRichiesta[]
	 */
	protected $variazioni;

	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Revoche\Revoca", mappedBy="attuazione_controllo_richiesta")
	 * @var Collection|Revoca[]
	 */
	protected $revoca;

	/**
	 * @ORM\ManyToMany(targetEntity="DocumentoBundle\Entity\DocumentoFile")
	 * @ORM\JoinTable(name="documenti_attuazione")    
	 */
	protected $documenti;

	/**
	 * @ORM\Column(name="cup", type="string", length=255, nullable=true)
	 */
	protected $cup;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_avvio;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_termine;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_avvio_effettivo;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_termine_effettivo;

	/**
	 * @var boolean $partenariato_pubblico_privato
	 * @ORM\Column(type="boolean", name="partenariato_pubblico_privato", nullable=true)
	 */
	protected $partenariato_pubblico_privato;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $maggiorazione_accettata;

	/** variabile di supporto per formtype non mappata, punta a oggetti di tipo DatiBancari
	 * @Assert\Valid()
	 */
	protected $datiBancariProponenti;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $procedure_aggiudicazione;

	/**
	 * @ORM\OneToMany(targetEntity="ProrogaRendicontazione", mappedBy="attuazione_controllo_richiesta")
	 * @var Collection|ProrogaRendicontazione[]
	 */
	protected $proroghe_rendicontazione;
	
	/**
	 * @var boolean $incremento_occupazionale_confermato
	 * @ORM\Column(type="boolean", name="incremento_occupazionale_confermato", nullable=true)
	 */
	protected $incremento_occupazionale_confermato;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Partita", mappedBy="attuazione_controllo_richiesta")
     * @var Collection|Partita[]
     */
    protected $partite;

	
	public function __construct() {
		$this->documenti = new ArrayCollection();
		$this->revoca = new ArrayCollection();
		$this->pagamenti = new ArrayCollection();
		$this->proroghe = new ArrayCollection();
		$this->variazioni = new ArrayCollection();
		$this->proroghe_rendicontazione = new ArrayCollection();
		$this->partite = new ArrayCollection();
	}

	public function getId() {
		return $this->id;
	}

	public function getRichiesta(): Richiesta {
		return $this->richiesta;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setRichiesta(Richiesta $richiesta):self {
		$this->richiesta = $richiesta;

		return $this;
	}

	public function getContributoAccettato() {
		return $this->contributo_accettato;
	}

	public function getDataLimiteAccettazione(): ?\DateTime {
		return $this->data_limite_accettazione;
	}

	public function setContributoAccettato($contributo_accettato):self {
		$this->contributo_accettato = $contributo_accettato;

		return $this;
	}

	public function setDataLimiteAccettazione($data_limite_accettazione):self {
		$this->data_limite_accettazione = $data_limite_accettazione;

		return $this;
	}

	public function getDocumentoAccettazione(): ?DocumentoFile {
		return $this->documento_accettazione;
	}

	public function getDataAccettazione(): ?\DateTime {
		return $this->data_accettazione;
	}

	public function getUtenteAccettazione() {
		return $this->utente_accettazione;
	}

	public function setDocumentoAccettazione(?DocumentoFile $documento_accettazione):self {
		$this->documento_accettazione = $documento_accettazione;

		return $this;
	}

	public function setDataAccettazione(?\DateTime $data_accettazione):self {
		$this->data_accettazione = $data_accettazione;

		return $this;
	}

	public function setUtenteAccettazione($utente_accettazione): self {
		$this->utente_accettazione = $utente_accettazione;

		return $this;
	}

	function setPagamenti(Collection $pagamenti):self {
		$this->pagamenti = $pagamenti;

		return $this;
	}

	function getProroghe(): Collection {
		return $this->proroghe;
	}

	function setProroghe(Collection $proroghe):self {
		$this->proroghe = $proroghe;

		return $this;
	}

	/**
	 * @return Collection|Pagamento[]
	 */
	function getPagamenti(): Collection {
		return $this->pagamenti;
	}

	public function isContributoAccettabile(): bool {
		//return is_null($this->getContributoAccettato()) && $this->getDataLimiteAccettazione() > new \DateTime();
		return \is_null($this->getContributoAccettato());
	}
    
    public function isContributoAccettato(): bool {
		return $this->getContributoAccettato() == true;
	}

	/**
	 * @param string|null $tipo filtro per classe di variazione
	 * @return VariazioneRichiesta[]|Collection
	 */
	public function getVariazioni(string $tipo = VariazioneRichiesta::class): Collection {
		$rfl = new \ReflectionClass($tipo);
		$variazioni = $this->variazioni->filter(function($variazione) use($rfl){
			return $rfl->isInstance($variazione);
		});

		return $variazioni;
	}

	public function setVariazioni(Collection $variazioni): self {
		$this->variazioni = $variazioni;

		return $this;
	}

	/**
	 * @return DocumentoFile[]|Collection
	 */
	public function getDocumenti(): Collection {
		return $this->documenti;
	}

	public function setDocumenti(Collection $documenti):self {
		$this->documenti = $documenti;

		return $this;
	}

	public function addDocumento(DocumentoFile $documento):self {
		$this->documenti->add($documento);

		return $this;
	}

	public function removeDocumento(DocumentoFile $documento): void {
		$this->documenti->removeElement($documento);
	}

	/**
	 * @param string $tipo Classe della variazione da considerare se non inserita prenderà solo variazioni piano costi
	 */
	public function getUltimaVariazioneApprovata(VariazioneRichiesta $variazione_rif = null, string $tipo = VariazionePianoCosti::class): ?VariazioneRichiesta {
		$ultima_variazione = null;
		/** @var VariazioneRichiesta $variazione */
		foreach ($this->getVariazioni($tipo) as $variazione) {
			if (
				(
					\is_null($variazione_rif) || 
					\is_null($variazione_rif->getDataInvio()) || 
					$variazione_rif->getDataInvio() > $variazione->getDataInvio()
				) && 
				$variazione->isEsitata())
			{
				$ultima_variazione = $variazione;
			}
		}

		return $ultima_variazione;
	}

	public function getUltimaVariazioneApprovataPrimaDellaData(?\DateTime $data_limite, string $tipo = VariazionePianoCosti::class): ?VariazioneRichiesta {
		$ultima_variazione = null;

		$ultima_data = null;
        foreach ($this->getVariazioni($tipo) as $variazione) {
            //$deb1 = $data_limite > $variazione->getDataInvio();		
            if ($data_limite > $variazione->getDataInvio() && $variazione->getEsitoIstruttoria() && count($variazione->getVociPianoCosto()) > 0 && (is_null($ultima_data) || $ultima_data < $variazione->getDataInvio()) && !$variazione->getIgnoraVariazione()) {
                $ultima_variazione = $variazione;
                $ultima_data = $variazione->getDataInvio();
            }
        }

        return $ultima_variazione;
    }
	

	public function getUltimaVariazionePianoCostiPA(Pagamento $pagamento): ?VariazionePianoCosti {
		$ultima_variazione = null;
		$procedura = $this->getProcedura();
		$ultima_data = null;
		
		/*
		 * Se il pagamento viene forzato a puntare ad una determinata variazione 
		 * non è necessario effettuare altri controlli
		 */
		if(!is_null($pagamento->getVariazione())) {
			return $pagamento->getVariazione();
		}
		
		foreach ($this->variazioni as $variazione) {
			/*
			 * Se la procedura non ha un comportamento particolare considero
			 * come riferimento la data di invio del pagamento e sarà
			 * efficace l'ultima variazione approvata prima dell'inoltro del pagamento
			 */
            if($variazione instanceof VariazionePianoCosti) {
                if ($procedura->isComportamentoParticolareVariazione() == false) {
                    if ((is_null($pagamento) || is_null($pagamento->getDataInvio()) || $pagamento->getDataInvio() > $variazione->getDataInvio()) && $variazione->getEsitoIstruttoria() && count($variazione->getVociPianoCosto()) > 0 && (is_null($ultima_data) || $ultima_data < $variazione->getDataInvio()) && !$variazione->getIgnoraVariazione()) {
                        $ultima_variazione = $variazione;
                        $ultima_data = $variazione->getDataInvio();
                    }
                }
                /*
                 * Se la procedura ha un comportamento particolare considero
                 * come riferimento la data della prima validazione della CK del pagamento e sarà
                 * efficace l'ultima variazione approvata precedentemente della prima validazione della CK pagamento
                 */
                else {
                    if ((is_null($pagamento) || is_null($pagamento->getDataPrimaValidazioneck()) || $pagamento->getDataPrimaValidazioneck() > $variazione->getDataInvio()) && $variazione->getEsitoIstruttoria() && count($variazione->getVociPianoCosto()) > 0 && (is_null($ultima_data) || $ultima_data < $variazione->getDataInvio()) && !$variazione->getIgnoraVariazione()) {
                        $ultima_variazione = $variazione;
                        $ultima_data = $variazione->getDataInvio();
                    }
                }
            }
		}

		return $ultima_variazione;
	}

	public function getUltimaProrogaAvvioApprovata($proroga_rif = null): ?Proroga {
		$ultima_proroga = null;
		$ultima_data = null;
		foreach ($this->proroghe as $proroga) {
			if ($proroga->getTipoProroga() == 'PROROGA_AVVIO') {
				if ((is_null($proroga_rif) || is_null($proroga_rif->getDataInvio()) || $proroga_rif->getDataInvio() > $proroga->getDataInvio()) && $proroga->getApprovata() && (is_null($ultima_data) || $ultima_data < $proroga->getDataInvio())) {
					$ultima_proroga = $proroga;
					$ultima_data = $proroga->getDataInvio();
				}
			}
		}

		return $ultima_proroga;
	}

	public function getUltimaProrogaFineApprovata($proroga_rif = null):?Proroga {
		$ultima_proroga = null;
		$ultima_data = null;
		foreach ($this->proroghe as $proroga) {
			if ($proroga->getTipoProroga() == 'PROROGA_FINE') {
				if ((is_null($proroga_rif) || is_null($proroga_rif->getDataInvio()) || $proroga_rif->getDataInvio() > $proroga->getDataInvio()) && $proroga->getApprovata() && (is_null($ultima_data) || $ultima_data < $proroga->getDataInvio())) {
					$ultima_proroga = $proroga;
					$ultima_data = $proroga->getDataInvio();
				}
			}
		}

		return $ultima_proroga;
	}

	public function setCup(?string $cup): self {
		$this->cup = $cup;

		return $this;
	}

	public function getCup(): ?string {
		return $this->cup;
	}

	public function addPagamenti(Pagamento $pagamenti): self {
		$this->pagamenti[] = $pagamenti;

		return $this;
	}

	public function removePagamenti(Pagamento $pagamenti): void {
		$this->pagamenti->removeElement($pagamenti);
	}

	public function addProroghe(Proroga $proroghe): self {
		$this->proroghe[] = $proroghe;

		return $this;
	}

	public function removeProroghe(Proroga $proroghe): void {
		$this->proroghe->removeElement($proroghe);
	}

	public function addVariazioni(VariazioneRichiesta $variazioni): self {
		$this->variazioni[] = $variazioni;

		return $this;
	}

	public function removeVariazioni(VariazioneRichiesta $variazioni): void {
		$this->variazioni->removeElement($variazioni);
	}

	public function addDocumenti(DocumentoFile $documenti): self {
		$this->documenti[] = $documenti;

		return $this;
	}

	public function removeDocumenti(DocumentoFile $documenti): void {
		$this->documenti->removeElement($documenti);
	}

	public function setDataAvvio(?\DateTime $dataAvvio): self {
		$this->data_avvio = $dataAvvio;

		return $this;
	}

	public function getDataAvvio(): ?\DateTime {
		return $this->data_avvio;
	}

	public function setDataTermine(?\DateTime $dataTermine):self {
		$this->data_termine = $dataTermine;

		return $this;
	}

	public function getDataTermine(): ?\DateTime  {
		return $this->data_termine;
	}

	public function setDataTermineEffettivo(?\DateTime $dataTermineEffettivo):self {
		$this->data_termine_effettivo = $dataTermineEffettivo;

		return $this;
	}

	public function getDataTermineEffettivo(): ?\DateTime {
		return $this->data_termine_effettivo;
	}

	public function getProcedura(): Procedura {
		return $this->getRichiesta()->getProcedura();
	}

	function getRevoca(): Collection {
		return $this->revoca;
	}

	function setRevoca(Collection $revoca): self {
		$this->revoca = $revoca;

		return $this;
	}

	public function hasPagamentoApprovatoConModalita(string $codice): bool {
		foreach ($this->pagamenti as $pagamento) {
			if ($pagamento->getModalitaPagamento()->getCodice() == $codice && $pagamento->getEsitoIstruttoria()) {
				return true;
			}
		}

		return false;
	}

	public function hasPagamentoUnicoApprovato(): bool {
		return $this->hasPagamentoApprovatoConModalita('UNICA_SOLUZIONE');
	}

	public function hasPagamentoSaldoApprovato(): bool {
		return $this->hasPagamentoApprovatoConModalita('SALDO_FINALE');
	}

	/** Verifica la presenza di variazioni pendenti
	 * @param string $tipo Nome della classe su cui fare la ricerca
	 */
	public function hasVariazionePendente(string $tipo = VariazioneRichiesta::class): bool {
		$variazioni = $this->getVariazioni($tipo)->toArray();
		$haVariazioniPendenti = \array_reduce(
			$variazioni,
			function(bool $carry, VariazioneRichiesta $variazione){
				return $carry || \is_null($variazione->getEsitoIstruttoria());
			},
			false
		);

		return $haVariazioniPendenti;
	}

	public function hasPagamentoPendente(): bool {
		foreach ($this->pagamenti as $pagamento) {
			if (\is_null($pagamento->getEsitoIstruttoria())) {
				return true;
			}
		}

		return false;
	}

	function getRevocaAttiva(): ?Revoca {
		foreach ($this->revoca as $revocaAttiva) {
			if (is_null($revocaAttiva->getDataCancellazione()))
				return $revocaAttiva;
		}
		return null;
	}

	public function setPartenariatoPubblicoPrivato(?bool $partenariatoPubblicoPrivato): self {
		$this->partenariato_pubblico_privato = $partenariatoPubblicoPrivato;

		return $this;
	}

	public function getPartenariatoPubblicoPrivato(): ?bool {
		return $this->partenariato_pubblico_privato;
	}

	public function getMaggiorazioneAccettata(): ?bool {
		return $this->maggiorazione_accettata;
	}

	public function setMaggiorazioneAccettata($maggiorazione_accettata): self {
		$this->maggiorazione_accettata = $maggiorazione_accettata;

		return $this;
	}

	public function hasMaggiorazioneAccettata(): bool {
		return $this->maggiorazione_accettata == true;
	}

	public function getUltimoPagamentoInviato(): ?Pagamento {
		$ultimo_pagamento = null;
		$data_ultimo_pagamento = null;
		$contatori = array();
		if (!is_null($this->pagamenti)) {
			foreach ($this->pagamenti as $pagamento) {
				if (is_null($pagamento->getDataInvio())) {
					continue;
				}

				$codice_modalita = $pagamento->getModalitaPagamento()->getCodice();

				if (!isset($contatori[$codice_modalita])) {
					$contatori[$codice_modalita] = 0;
				}

				$contatori[$codice_modalita] ++;
				$pagamento->setContatore($contatori[$codice_modalita]);

				if (is_null($data_ultimo_pagamento) || $pagamento->getDataInvio() > $data_ultimo_pagamento) {
					$ultimo_pagamento = $pagamento;
					$data_ultimo_pagamento = $pagamento->getDataInvio();
				}
			}
		}

		return $ultimo_pagamento;
	}

	// usata in Ing Finanziaria
	public function hasPagamentoTrasferimento(): bool {
		foreach ($this->pagamenti as $pagamento) {
			if ($pagamento->getModalitaPagamento()->getCodice() == ModalitaPagamento::TRASFERIMENTO) {
				return true;
			}
		}
		return false;
	}

	public function getDatiBancariProponenti() {
		return $this->datiBancariProponenti;
	}

	public function setDatiBancariProponenti($datiBancariProponenti) {
		$this->datiBancariProponenti = $datiBancariProponenti;
	}
	
	public function hasRevocaConRecuperoConclusa(): bool {
		foreach ($this->revoca as $rev) {
			if($rev->hasRecuperoCompleto()) {
				return true;
			}
		}
		return false;
	}

	public function setProcedureAggiudicazione(bool $procedureAggiudicazione): self {
		$this->procedure_aggiudicazione = $procedureAggiudicazione;

		return $this;
	}

	public function getProcedureAggiudicazione():?bool {
		return $this->procedure_aggiudicazione;
	}

	/**
	 * @return bool
	 */
	public function isIncrementoOccupazionaleConfermato(): ?bool
	{
		return $this->incremento_occupazionale_confermato;
	}

	/**
	 * @param bool $incremento_occupazionale_confermato
	 */
	public function setIncrementoOccupazionaleConfermato(?bool $incremento_occupazionale_confermato): void
	{
		$this->incremento_occupazionale_confermato = $incremento_occupazionale_confermato;
	}


	public function addRevoca(Revoca $revoca): self {
		$this->revoca[] = $revoca;

		return $this;
	}

	public function removeRevoca(Revoca $revoca):void  {
		$this->revoca->removeElement($revoca);
	}
	
	public function getImportoRendicontatoAmmessoTotale(): float {
		return \array_reduce($this->getPagamenti()->toArray(), function(float $carry, Pagamento $pagamento){
			return $carry + $pagamento->getImportoRendicontatoAmmesso();
		}, 0.0);
	}
	
	public function isExArticolo137() {
		foreach ($this->revoca as $rev) {
			if($rev->getArticolo137() == true) {
				return true;
			}
		}
		return false;
	}
	
	public function getDataAvvioEffettivo() {
		return $this->data_avvio_effettivo;
	}

	public function setDataAvvioEffettivo($data_avvio_effettivo) {
		$this->data_avvio_effettivo = $data_avvio_effettivo;
	}
	
	public function getCostoAmmesso() {
		$variazione = $this->getUltimaVariazioneApprovata();
		$costoAmmessoIstruttoria = $this->richiesta->getIstruttoria()->getCostoAmmesso();

		return \is_null($variazione) ?
			$costoAmmessoIstruttoria :
			$variazione->getCostoAmmesso() ?: $costoAmmessoIstruttoria;
	}
	
	public function getCostoAmmessoIstruttoria() {
		return $this->richiesta->getIstruttoria()->getCostoAmmesso();
	}

	public function getContributoConcesso(){
		$variazione = $this->getUltimaVariazioneApprovata();
		$contributoAmmessoIstruttoria = $this->richiesta->getIstruttoria()->getContributoAmmesso();

		return \is_null($variazione) ?
			$contributoAmmessoIstruttoria :
			$variazione->getContributoAmmesso() ?: $contributoAmmessoIstruttoria;
	}

	public function getContributoErogato(): float{
		return \array_reduce($this->getPagamenti()->toArray(), function(float $carry, Pagamento $pagamento){
			return $carry + $pagamento->getContributoErogato();
		}, 0.0);
	}

	public function getProrogaRendicontazione(ModalitaPagamento $modalita, ?\DateTime $dataRif = null): ?ProrogaRendicontazione {
		$dataRif = $dataRif ?: new \DateTime('now');
        $prorogheModalita = $this->proroghe_rendicontazione->filter(
            function (ProrogaRendicontazione $rend) use ($modalita) {
                return $rend->getModalitaPagamento() == $modalita;
            }
        );
        $prorogheIntervalloDate = $prorogheModalita->filter(
            function (ProrogaRendicontazione $pr) use ($dataRif) {
                return $pr->isRendicontabile($dataRif);
            }
        );

        return $prorogheIntervalloDate->last() ?: null;
	}

	public function hasProrogaRendicontazione(ModalitaPagamento $modalita, ?\DateTime $dataRif = null): bool {
        return !\is_null($this->getProrogaRendicontazione($modalita, $dataRif));
    }

    public function addProrogheRendicontazione(ProrogaRendicontazione $prorogheRendicontazione): self {
        $this->proroghe_rendicontazione[] = $prorogheRendicontazione;

        return $this;
    }

    public function removeProrogheRendicontazione(ProrogaRendicontazione $prorogheRendicontazione): void {
        $this->proroghe_rendicontazione->removeElement($prorogheRendicontazione);
    }

    /**
     * @return ProrogaRendicontazione[]|Collection
     */
    public function getProrogheRendicontazione():Collection {
        return $this->proroghe_rendicontazione;
    }

    /**
     * @return Partita[]|Collection
     */
    public function getPartite()
    {
        return $this->partite;
    }

    /**
     * @param Partita[]|Collection $partite
     */
    public function setPartite($partite): void
    {
        $this->partite = $partite;
    }

    /**
     * @return DateTime|null
     */
    public function getDataAvvioProgettoConEventualeProroga()
    {
        /* Se è presente una data invio nella configurazione della rendicontazione
           prendo quella data altrimenti proseguo.
        */
        if ($this->getProcedura()->getRendicontazioneProceduraConfig()->getDataInizioProgetto()) {
            $dataAvvioProgetto = $this->getProcedura()->getRendicontazioneProceduraConfig()->getDataInizioProgetto();
        } else {
            /* Se è presente una proroga approvata la prendo come data da restituire, 
              altrimenti prendo la data da istruttoria richiesta (date impostate nel passaggio in ATC).
            */
            if (!is_null($this->getUltimaProrogaAvvioApprovata())) {
                $dataAvvioProgetto = $this->getUltimaProrogaAvvioApprovata()->getDataAvvioApprovata();
            } else {
                $istruttoria = $this->getRichiesta()->getIstruttoria();
                $dataAvvioProgetto = $istruttoria->getDataAvvioProgetto();
            }
        }

        return $dataAvvioProgetto;
    }

    /**
     * @return DateTime|null
     */
    public function getDataTermineProgettoConEventualeProroga()
    {
        /* Se è presente una proroga approvata la prendo come data da restituire, 
           altrimenti prendo la data da istruttoria richiesta (date impostate nel passaggio in ATC) 
        */
        if (!is_null($this->getUltimaProrogaFineApprovata())) {
            $dataTermineProgetto = $this->getUltimaProrogaFineApprovata()->getDataFineApprovata();
        } else {
            $istruttoria = $this->getRichiesta()->getIstruttoria();
            $dataTermineProgetto = $istruttoria->getDataTermineProgetto();
        }

        return $dataTermineProgetto;
    }
    
    public function isRevocatoTotale() {
        foreach($this->revoca as $rev) {
            if(!is_null($rev->getAttoRevoca())) {
                return $rev->getAttoRevoca()->getTipo()->getCodice() == 'TOT';
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasQuestionarioRSI(): bool
    {
        if ($this->getProcedura()->getRendicontazioneProceduraConfig()->getSezioneRSI()
            && $this->getRichiesta()->getMandatario()->getSoggetto()->getTipo() == 'AZIENDA' && !in_array($this->getProcedura()->getId(), [8, 32])) {
            return true;
        } else {
            return false;
        }
    }
}
