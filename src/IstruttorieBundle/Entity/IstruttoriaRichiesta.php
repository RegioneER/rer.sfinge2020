<?php

namespace IstruttorieBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use SfingeBundle\Entity\Atto;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\RichiestaCupBatch;
use BaseBundle\Annotation as Sfinge;
use CipeBundle\Entity\Classificazioni\CupCategoria;
use Doctrine\Common\Collections\Collection;
use RichiesteBundle\Entity\Richiesta;
use CipeBundle\Entity\Classificazioni\CupNatura;
use CipeBundle\Entity\Classificazioni\CupSettore;
use CipeBundle\Entity\Classificazioni\CupSottosettore;
use CipeBundle\Entity\Classificazioni\CupTipoCoperturaFinanziaria;
use CipeBundle\Entity\Classificazioni\CupTipologia;
use SfingeBundle\Entity\Procedura;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Entity\VocePianoCosto;
use SfingeBundle\Entity\Utente;

/**
 * IstruttoriaRichiesta
 *
 * @ORM\Table(name="istruttorie_richieste")
 * @ORM\Entity(repositoryClass="IstruttorieBundle\Entity\IstruttoriaRichiestaRepository")
 */
class IstruttoriaRichiesta extends EntityLoggabileCancellabile {
	
	// tipologia soggetto relativo alla richiesta (settato durante il passaggio in ATC)
	//aggiornamento: era prtita bene la cosa, adesso ho l'impressione che non servirà più a nulla..vedremo..
	const PRIVATO = 'PRIVATO';
	const PUBBLICO = 'PUBBLICO';

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @var int|null
	 */
	protected $id;

	/**
	 * @ORM\OneToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="istruttoria")
	 * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id")
	 * @var Richiesta|null
	 */
	protected $richiesta;

	/**
	 * @ORM\Column(name="costo_ammesso", type="decimal", precision=10, scale=2, nullable=true)
	 * @var string|float|null
	 */
	protected $costo_ammesso;

	/**
	 * @ORM\Column(name="contributo_ammesso", type="decimal", precision=10, scale=2, nullable=true)
	 * @var string|float|null
	 */
	protected $contributo_ammesso;

	/**
	 * @ORM\Column(type="boolean")
	 * @var bool
	 */
	protected $richiedi_cup = false;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @var string|null
	 */
	protected $validazione;

	/**
	 * @ORM\Column(type="string", length=50, nullable=true)
	 * @Assert\NotNull(groups="procedure_particolari")
	 * @var string|null
	 */
	protected $codice_cup;

	/**
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\ValutazioneChecklistIstruttoria", mappedBy="istruttoria", cascade={"persist"})
	 * @var Collection|ValutazioneChecklistIstruttoria[]
	 */
	protected $valutazioni_checklist;

	/**
	 * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\FaseIstruttoria")
	 * @ORM\JoinColumn(nullable=true)
	 * @Sfinge\CampoStato()
	 * @var FaseIstruttoria|null
	 */
	protected $fase;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @var string|null
	 */
	protected $note;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 * @Assert\NotNull(groups="esito_finale")
	 * @var DateTime|null
	 */
	protected $data_verbalizzazione;

	/**
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\IstruttoriaLog", mappedBy="istruttoria_richiesta", cascade={"persist"})
	 * @ORM\OrderBy({"data" = "DESC"})
	 * @var Collection|IstruttoriaLog[]
	 */
	protected $istruttorie_log;
	
	/**
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\IstruttoriaAtcLog", mappedBy="istruttoria_richiesta", cascade={"persist"})
	 * @ORM\OrderBy({"data" = "DESC"})
	 * @var Collection|IstruttoriaAtcLog[]
	 */
	protected $istruttorie_atc_log;

	/**
	 * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\EsitoIstruttoria")
	 * @ORM\JoinColumn(nullable=true)
	 * @Assert\NotNull(groups="esito_finale")
	 * @var EsitoIstruttoria|null
	 */
	protected $esito;

	/**
	 * @ORM\ManyToOne(targetEntity="CipeBundle\Entity\Classificazioni\CupNatura")
	 * @ORM\JoinColumn(nullable=true)
	 * @Assert\NotNull(groups={"procedure_particolari", "dati_cup","avanzamento_atc"}, message="La natura CUP è obbligatoria")
	 * @var CupNatura|null
	 */
	protected $cup_natura;

	/**
	 * @ORM\ManyToOne(targetEntity="CipeBundle\Entity\Classificazioni\CupTipologia")
	 * @ORM\JoinColumn(nullable=true)
	 * @Assert\NotNull(groups={"procedure_particolari", "dati_cup","avanzamento_atc"}, message="La tipologia CUP è obbligatoria")
	 * @var CupTipologia|null
	 */
	protected $cup_tipologia;

	/**
	 * @ORM\ManyToOne(targetEntity="CipeBundle\Entity\Classificazioni\CupSettore")
	 * @ORM\JoinColumn(nullable=true)
	 * @var CupSettore|null
	 */
	protected $cup_settore;

	/**
	 * @ORM\ManyToOne(targetEntity="CipeBundle\Entity\Classificazioni\CupSottosettore")
	 * @ORM\JoinColumn(nullable=true)
	 * @var CupSottosettore|null
	 */
	protected $cup_sottosettore;

	/**
	 * @ORM\ManyToOne(targetEntity="CipeBundle\Entity\Classificazioni\CupCategoria")
	 * @ORM\JoinColumn(nullable=true)
	 * @var CupCategoria|null
	 */
	protected $cup_categoria;

	/**
	 * @ORM\ManyToMany(targetEntity="CipeBundle\Entity\Classificazioni\CupTipoCoperturaFinanziaria")
	 * @ORM\JoinTable(name="istruttorie_cup_tipi_copertura_richieste")
	 * @var CupTipoCoperturaFinanziaria|null
	 */
	protected $cup_tipi_copertura_finanziaria;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @Assert\NotNull(groups={"avanzamento_atc"})
	 * @var bool|null
	 */
	protected $ammissibilita_atto;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @Assert\NotNull(groups={"avanzamento_atc"})
	 * @var bool|null
	 */
	protected $concessione;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @var bool|null
	 */
	protected $validazione_atc;

	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente")
	 * @ORM\JoinColumn(nullable=true)
	 * @var Utente|null
	 */
	protected $utente_validatore_atc;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @var DateTime|null
	 */
	protected $data_validazione_atc;

	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Atto")
	 * @ORM\JoinColumn(nullable=true)
     * @var Atto
	 */
	protected $atto_ammissibilita_atc;

	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Atto")
	 * @ORM\JoinColumn(nullable=true)
     * @var Atto
	 */
	protected $atto_concessione_atc;

	/**
	 * @var RichiestaCupBatch
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\RichiestaCupBatch", inversedBy="IstruttorieGenerate")
	 * @ORM\JoinColumn(name="ultimarichiestacupbatch_id", referencedColumnName="id", nullable=true)
	 */
	protected $UltimaRichiestaCupBatch;

	/**
	 * @var RichiestaCupBatch
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\RichiestaCupBatch", inversedBy="IstruttorieValorizzate")
	 * @ORM\JoinColumn(name="richiestacupbatchrisposta_id", referencedColumnName="id", nullable=true)
	 */
	protected $RichiestaCupBatchRisposta;

	/**
	 * @var RichiestaCupBatch
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\RichiestaCupBatch", inversedBy="IstruttorieScartate")
	 * @ORM\JoinColumn(name="ultimarichiestacupbatchscarto_id", referencedColumnName="id", nullable=true)
	 */
	protected $UltimaRichiestaCupBatchScarto;

	/**
	 * @var boolean $maggiorazione_contributo_occupazionale
	 * @ORM\Column(type="boolean", name="maggiorazione_contributo_occupazionale", nullable=true)
	 */
	protected $maggiorazione_contributo_occupazionale;
	
    /**
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\IntegrazioneIstruttoria", mappedBy="istruttoria")
	 * @var Collection|IntegrazioneIstruttoria[]
	 */
	protected $integrazioni;
	
	/**
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoria", mappedBy="istruttoria")
	 * @var Collection|ComunicazioneEsitoIstruttoria[]
	 */
	protected $comunicazioni_esiti;

	/**
	 * @ORM\Column(type="boolean", nullable=false)
	 * @var bool|null
	 */
	protected $sospesa;

	/**
	 * @ORM\OneToOne(targetEntity="IstruttorieBundle\Entity\NucleoIstruttoria", mappedBy="istruttoriaRichiesta")
	 * @var NucleoIstruttoria|null  
	 */
	protected $nucleoIstruttoria;
	// nota: data avvio, termine, inizio vincolante e tipologia soggetto avrebbero dovuto stare in ATC
	// però per com'era costruita l'action abbiamo deciso di metterle qui per minimizzare l'impatto

	/**
     * @ORM\Column(type="date", nullable=true)
	 * @var DateTime|null
     */
    protected $data_avvio_progetto;

    /**
     * @ORM\Column(type="date", nullable=true)
	 * @var DateTime|null
     */
    protected $data_termine_progetto;
	
	/**
     * @ORM\Column(type="boolean", name="data_inizio_vincolante", nullable=true)
	 * @var bool|null
     */
    protected $data_inizio_vincolante;
	
	/**
     * @ORM\Column(name="tipologia_soggetto", type="string", length=16, nullable=true)
	 * @var string|null
     */
    protected $tipologia_soggetto;

	/**
	 * @ORM\Column(name="impegno_ammesso", type="decimal", precision=10, scale=2, nullable=true)
	 * @var string|float|null
	 */
	protected $impegno_ammesso;

	/**
	 * @ORM\Column(type="date", name="data_contributo", nullable=true)
	 * @Assert\Date()
	 * @var DateTime|null
	 */
	protected $data_contributo;

	/**
	 * @ORM\Column(type="date", name="data_impegno", nullable=true)
	 * @Assert\Date()
	 * @var DateTime|null
	 */
	protected $data_impegno;

    /**
     * @ORM\Column(type="string", length=32, name="numero_impegno", nullable=true)
     * @var string|null
     */
    private $numero_impegno;

    /**
     * @var int|null
     * @ORM\Column(type="smallint", name="posizione_impegno", nullable=true)
     */
    private $posizione_impegno;

    /**
     * @ORM\Column(type="string", name="cor", nullable=true, length=50)
     * @var string|null
     */
    protected $cor;

	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Atto")
	 * @ORM\JoinColumn(nullable=true)
	 * @var Atto|null
	 */
	protected $atto_modifica_concessione_atc;

    /**
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\PropostaImpegno", mappedBy="istruttoriaRichiesta", cascade={"persist"})
     * @var Collection|PropostaImpegno[]
     */
    protected $proposte_impegno;

	
	function __construct() {
		$this->valutazioni_checklist = new ArrayCollection();
		$this->istruttorie_log = new ArrayCollection();
		$this->istruttorie_atc_log = new ArrayCollection();
		$this->integrazioni = new ArrayCollection();
		$this->comunicazioni_esiti = new ArrayCollection();
	}

	/**
	 * @return string|float|null 
	 */
	public function getContributoAmmesso() {
		return $this->contributo_ammesso;
	}

	public function setRichiediCup(bool $richiediCup): self {
		$this->richiedi_cup = $richiediCup;

		return $this;
	}

	/**
	 * @param string $validazione
	 * @return IstruttoriaRichiesta
	 */
	public function setValidazione($validazione) {
		$this->validazione = $validazione;

		return $this;
	}

    public function getRichiediCup(): bool
    {
        return $this->richiedi_cup;
    }

    public function getValidazione(): ?string
    {
        return $this->validazione;
    }

    public function setCodiceCup(?string $codiceCup): self
    {
        $this->codice_cup = $codiceCup;

        return $this;
    }

    public function getCodiceCup(): ?string
    {
        return $this->codice_cup;
    }

	function getId() {
		return $this->id;
	}

	function getRichiesta(): ?Richiesta {
		return $this->richiesta;
	}

	function getCostoAmmesso() {
		return $this->costo_ammesso;
	}

	/**
	 * @return Collection|ValutazioneChecklistIstruttoria[]
	 */
	function getValutazioniChecklist(): Collection {
		return $this->valutazioni_checklist;
	}

	function getFase(): ?FaseIstruttoria {
		return $this->fase;
	}

	function getNote(): ?string {
		return $this->note;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setRichiesta(?Richiesta $richiesta): self {
		$this->richiesta = $richiesta;

		return $this;
	}

	function setCostoAmmesso($costo_ammesso): self {
		$this->costo_ammesso = $costo_ammesso;

		return $this;
	}

	function setContributoAmmesso($contributo_ammesso) {
		$this->contributo_ammesso = $contributo_ammesso;
	}

	function setValutazioniChecklist($valutazioni_checklist) {
		$this->valutazioni_checklist = $valutazioni_checklist;
	}

	function setFase(?FaseIstruttoria $fase): self {
		$this->fase = $fase;

		return $this;
	}

	function setNote(?string $note) {
		$this->note = $note;
	}

	function addValutazioneChecklist(ValutazioneChecklistIstruttoria $valutazione_checklist): self {
		$this->valutazioni_checklist->add($valutazione_checklist);
		$valutazione_checklist->setIstruttoria($this);

		return $this;
	}

	function getDataVerbalizzazione(): ?DateTime {
		return $this->data_verbalizzazione;
	}

	function setDataVerbalizzazione(?DateTime $data_verbalizzazione):self {
		$this->data_verbalizzazione = $data_verbalizzazione;

		return $this;
	}

	function getEsito(): ?EsitoIstruttoria {
		return $this->esito;
	}

	function setEsito(?EsitoIstruttoria $esito): self {
		$this->esito = $esito;

		return $this;
	}

	function getCupNatura(): ?CupNatura {
		return $this->cup_natura;
	}

	function getCupTipologia(): ?\CipeBundle\Entity\Classificazioni\CupTipologia {
		return $this->cup_tipologia;
	}

	function getCupSettore(): ?CupSettore {
		return $this->cup_settore;
	}

	function getCupSottosettore(): ?CupSottosettore {
		return $this->cup_sottosettore;
	}

	function getCupCategoria() {
		return $this->cup_categoria;
	}

	/**
	 * @return Collection
	 */
	function getCupTipiCoperturaFinanziaria() {
		return $this->cup_tipi_copertura_finanziaria;
	}

	function setCupNatura($cup_natura) {
		$this->cup_natura = $cup_natura;
	}

	function setCupTipologia($cup_tipologia) {
		$this->cup_tipologia = $cup_tipologia;
	}

	function setCupSettore($cup_settore) {
		$this->cup_settore = $cup_settore;
	}

	function setCupSottosettore($cup_sotto_settore) {
		$this->cup_sottosettore = $cup_sotto_settore;
	}

	function setCupCategoria($cup_categoria) {
		$this->cup_categoria = $cup_categoria;
	}

	/**
	 * @return self
	 */
	function setCupTipiCoperturaFinanziaria($cup_tipi_copertura_finanziaria) {
		$this->cup_tipi_copertura_finanziaria = $cup_tipi_copertura_finanziaria;
		return $this;
	}

	function getValutazioniByChecklist(ChecklistIstruttoria $checklist): Collection {
		$valutazioni = $this->getValutazioniChecklist()->filter(function(ValutazioneChecklistIstruttoria $v) use($checklist){
			return $v->getChecklist() == $checklist;
		});

		return $valutazioni;
	}

	function hasValutazioneNegativa() {
		foreach ($this->getValutazioniChecklist() as $valutazione) {
			if ($valutazione->getAmmissibile() === false) {
				return false;
			}
		}

		return true;
	}

	function isCupCompleto() {
		if (is_null($this->cup_natura)) {
			return false;
		}
		if (is_null($this->cup_tipologia)) {
			return false;
		}
		if (is_null($this->cup_settore)) {
			return false;
		}
		if (is_null($this->cup_sottosettore)) {
			return false;
		}
		if (is_null($this->cup_categoria)) {
			return false;
		}
		if (is_null($this->cup_tipi_copertura_finanziaria) || count($this->cup_tipi_copertura_finanziaria) == 0) {
			return false;
		}

		return true;
	}

	function getArrayCodiciCupTipiCopertura() {
		$codici = array();
		foreach ($this->getCupTipiCoperturaFinanziaria() as $tipo) {
			$codici[] = $tipo->getCodice();
		}

		return $codici;
	}

	function getUltimaRichiestaCupBatch() {
		return $this->UltimaRichiestaCupBatch;
	}

	function getRichiestaCupBatchRisposta() {
		return $this->RichiestaCupBatchRisposta;
	}

	function getUltimaRichiestaCupBatchScarto() {
		return $this->UltimaRichiestaCupBatchScarto;
	}

	function setUltimaRichiestaCupBatch($UltimaRichiestaCupBatch) {
		$this->UltimaRichiestaCupBatch = $UltimaRichiestaCupBatch;
	}

	function setRichiestaCupBatchRisposta($RichiestaCupBatchRisposta) {
		$this->RichiestaCupBatchRisposta = $RichiestaCupBatchRisposta;
	}

	function setUltimaRichiestaCupBatchScarto($UltimaRichiestaCupBatchScarto) {
		$this->UltimaRichiestaCupBatchScarto = $UltimaRichiestaCupBatchScarto;
	}

	function getUltimoScarto() {
		$UltimaRichiestaCupBatchScarto = $this->getUltimaRichiestaCupBatchScarto();
		return $UltimaRichiestaCupBatchScarto->getScartiOfIdProgetto($this->getRichiesta()->getId());
	}

	function getIstruttorieLog() {
		return $this->istruttorie_log;
	}

	function setIstruttorieLog($istruttorie_log) {
		$this->istruttorie_log = $istruttorie_log;
	}

	function addIstruttoriaLog($istruttoria_log) {
		$this->istruttorie_log->add($istruttoria_log);
		$istruttoria_log->setIstruttoriaRichiesta($this);
	}


	function getSospesa() {
		return $this->sospesa;
	}

	public function getSoggetto() {
		return $this->getRichiesta()->getSoggetto();
	}

	function getAmmissibilitaAtto() {
		return $this->ammissibilita_atto;
	}

	function getConcessione() {
		return $this->concessione;
	}

	function getValidazioneAtc() {
		return $this->validazione_atc;
	}

	function getUtenteValidatoreAtc() {
		return $this->utente_validatore_atc;
	}

	function getDataValidazioneAtc() {
		return $this->data_validazione_atc;
	}

	function setAmmissibilitaAtto($ammissibilita_atto) {
		$this->ammissibilita_atto = $ammissibilita_atto;
	}

	function setConcessione($concessione) {
		$this->concessione = $concessione;
	}

	function setValidazioneAtc($validazione_atc) {
		$this->validazione_atc = $validazione_atc;
	}

	function setUtenteValidatoreAtc($utente_validatore_atc) {
		$this->utente_validatore_atc = $utente_validatore_atc;
	}

	function setDataValidazioneAtc($data_validazione_atc) {
		$this->data_validazione_atc = $data_validazione_atc;
	}

	public function getMaggiorazioneContributoOccupazionale() {
		return $this->maggiorazione_contributo_occupazionale;
	}

	public function setMaggiorazioneContributoOccupazionale($maggiorazione_contributo_occupazionale) {
		$this->maggiorazione_contributo_occupazionale = $maggiorazione_contributo_occupazionale;
	}

	public function getProcedura(): Procedura {
		$procedura = $this->richiesta->getProcedura();
		return $procedura;
	}

	function getAttoAmmissibilitaAtc() {
		return $this->atto_ammissibilita_atc;
	}

	function getAttoConcessioneAtc() {
		return $this->atto_concessione_atc;
	}

	function setAttoAmmissibilitaAtc($atto_ammissibilita_atc) {
		$this->atto_ammissibilita_atc = $atto_ammissibilita_atc;
	}

	function setAttoConcessioneAtc($atto_concessione_atc) {
		$this->atto_concessione_atc = $atto_concessione_atc;
	}

	function setSospesa($sospesa) {
		$this->sospesa = $sospesa;
	}
	
	function isSospesa(){
		return $this->sospesa == true;
	}

	function getNucleoIstruttoria(){
		return $this->nucleoIstruttoria;
	}

	function setNucleoIstruttoria( $value ){
		$this->nucleoIstruttoria = $value;
	}
	
	public function getComunicazioniEsiti() {
		return $this->comunicazioni_esiti;
	}

	public function setComunicazioniEsiti($comunicazioni_esiti) {
		$this->comunicazioni_esiti = $comunicazioni_esiti;
	}
	
	public function getIntegrazioni() {
		return $this->integrazioni;
	}

	public function setIntegrazioni($integrazioni) {
		$this->integrazioni = $integrazioni;
	}

	public function comunicazioniAbilitate() {
		foreach ($this->comunicazioni_esiti as $comunicazione) {
			if($comunicazione->isInAttesaRisposta()) {
				return false;
			}
			if($comunicazione->getStato() != 'ESI_PROTOCOLLATA' ) {
				return false;
			}
		}
		return true;
	}	

	public function getDataInizioVincolante(): ?bool {
		return $this->data_inizio_vincolante;
	}

	public function getTipologiaSoggetto() {
		return $this->tipologia_soggetto;
	}

	public function setDataInizioVincolante(?bool $data_inizio_vincolante) {
		$this->data_inizio_vincolante = $data_inizio_vincolante;
	}

	public function setTipologiaSoggetto($tipologia_soggetto): self {
        $this->tipologia_soggetto = $tipologia_soggetto;

        $procedura = $this->getProcedura();

        $pubblico = false;
        switch ($procedura->getMonTipoBeneficiario()) {
            case Procedura::MON_TIPO_PRG_PRIVATO:
                $pubblico = false;
            break;
            case Procedura::MON_TIPO_PRG_PUBBLICO:
                $pubblico = true;
            break;
            case Procedura::MON_TIPO_PRG_MISTO:
            default:
                $pubblico = self::PUBBLICO == $tipologia_soggetto;
            break;
        }
        $this->richiesta->setMonPrgPubblico($pubblico);

        return $this;
	}
	
	public function getDataAvvioProgetto(): ?DateTime {
		return $this->data_avvio_progetto;
	}

	public function getDataTermineProgetto(): ?DateTime {
		return $this->data_termine_progetto;
	}

	public function setDataAvvioProgetto(?DateTime  $data_avvio_progetto) {
		$this->data_avvio_progetto = $data_avvio_progetto;
	}

	public function setDataTermineProgetto(?DateTime  $data_termine_progetto) {
		$this->data_termine_progetto = $data_termine_progetto;
	}	
		
	public function isSoggettoPrivato(): bool {
		return $this->tipologia_soggetto == self::PRIVATO;
	}
	
	public function isSoggettoPubblico(): bool {
		return $this->tipologia_soggetto == self::PUBBLICO;
	}
	
	public function isDataInizioVincolante(): bool {
		return $this->data_inizio_vincolante == true;
	}
	
	public function getImpegnoAmmesso() {
		return $this->impegno_ammesso;
	}

	public function getDataContributo(): ?DateTime {
		return $this->data_contributo;
	}

	public function getDataImpegno(): ?DateTime {
		return $this->data_impegno;
	}

    /**
     * @return string|null
     */
    public function getNumeroImpegno(): ?string
    {
        return $this->numero_impegno;
    }

    /**
     * @return int|null
     */
    public function getPosizioneImpegno(): ?int
    {
        return $this->posizione_impegno;
    }

    /**
     * @return string|null
     */
    public function getCor(): ?string
    {
        return $this->cor;
    }

	public function setImpegnoAmmesso($impegno_ammesso) {
		$this->impegno_ammesso = $impegno_ammesso;
	}

    /**
     * @param int|null $posizione_impegno
     */
    public function setPosizioneImpegno(?int $posizione_impegno): void
    {
        $this->posizione_impegno = $posizione_impegno;
    }

	public function setDataContributo(?DateTime $data_contributo) {
		$this->data_contributo = $data_contributo;
	}

	public function setDataImpegno(?DateTime  $data_impegno) {
		$this->data_impegno = $data_impegno;
	}

    /**
     * @param string|null $numero_impegno
     */
    public function setNumeroImpegno(?string $numero_impegno): void
    {
        $this->numero_impegno = $numero_impegno;
    }

    /**
     * @param string|null $cor
     */
    public function setCor(?string $cor): void
    {
        $this->cor = $cor;
    }

	public function getIstruttorieAtcLog() {
		return $this->istruttorie_atc_log;
	}

	public function setIstruttorieAtcLog($istruttorie_atc_log) {
		$this->istruttorie_atc_log = $istruttorie_atc_log;
	}
	
	function addIstruttoriaAtcLog($istruttorie_atc_log) {
		$this->istruttorie_atc_log->add($istruttorie_atc_log);
		$istruttorie_atc_log->setIstruttoriaRichiesta($this);
	}
	
	public function getAttoModificaConcessioneAtc() {
		return $this->atto_modifica_concessione_atc;
	}

	public function setAttoModificaConcessioneAtc($atto_modifica_concessione_atc) {
		$this->atto_modifica_concessione_atc = $atto_modifica_concessione_atc;
	}
    
    public function isAmmesso() {
        if(!is_null($this->esito)) {
            return $this->esito->getCodice() == 'AMMESSO';
        }
        return false;
    }

    /**
     * @Assert\IsTrue(message="Natura cup o tipologia cup non compilata", groups={"esito_finale"})
     */
    public function isDatiCupRichiestiValid()
    {
        if (!$this->getProcedura()->isSezioneIstruttoriaCup()) {
            return true;
        }
        
        return $this->isAmmesso() == false || ( !is_null($this->cup_natura) && !is_null($this->cup_tipologia));
    }
    
    public function isConcessaAtc() {
        if(!is_null($this->getRichiesta()->getAttuazioneControllo()) && $this->validazione_atc == 1 && $this->concessione == 1) {
            return true;
        }
        return false;
	}
	
	public function getTotaleAmmesso(): float {
		/** @var float[] $totale */
		$totali = $this->richiesta->getVociPianoCosto()
		->filter(function(VocePianoCosto $voce): bool{
			$tipoVoceSpesa = $voce->getPianoCosto()->getTipoVoceSpesa();
			return $tipoVoceSpesa->getCodice() == 'TOTALE';
		})
		->map(function(VocePianoCosto $voce){
			return !is_null($voce->getIstruttoria()) ? $voce->getIstruttoria()->sommaImportiAvanzamento() : 0;
		})->toArray();
        
        $totale = \array_reduce($totali, function($carry, $voce){
            return $carry + $voce;
        }, 0.0);

		return $totale ?: 0.0;
	}

    /**
     * @param $sezione
     * @return float
     */
    public function getTotaleAmmessoPerSezione($sezione): float
    {
        /** @var float[] $totale */
        $totali = $this->richiesta->getVociPianoCosto()
            ->filter(function(VocePianoCosto $voce) use ($sezione): bool {
                $tipoVoceSpesa = $voce->getPianoCosto()->getTipoVoceSpesa();
                return $tipoVoceSpesa->getCodice() == 'TOTALE'
                    && $voce->getPianoCosto()->getSezionePianoCosto()->getCodice() == $sezione;
            })
            ->map(function(VocePianoCosto $voce) {
                return $voce->getIstruttoria()->sommaImportiAvanzamento();
            })->toArray();

        $totale = \array_reduce($totali, function($carry, $voce) {
            return $carry + $voce;
        }, 0.0);

        return $totale ?: 0.0;
    }

    /**
     * @return array|DocumentoIstruttoria[]
     */
    public function getDocumentiIstruttoria(): array
    {
        return $this->getRichiesta()->getDocumentiIstruttoria();
    }

    /**
     * @param IstruttoriaRichiesta $istruttoriaRichiesta
     * @param int $numeroAnnualita
     * @return bool
     */
    public function hasPianoCostoAmmesso(IstruttoriaRichiesta $istruttoriaRichiesta, int $numeroAnnualita): bool
    {
        $sommaImportiAmmessi = array_fill(1, $numeroAnnualita, 0.00);
        $sommaImportiTagliati = array_fill(1, $numeroAnnualita, 0.0);

        /** @var VocePianoCosto[] $vociPianoCostoPresentazione */
        $vociPianoCostoPresentazione = $istruttoriaRichiesta->getRichiesta()->getVociPianoCosto();
        foreach ($vociPianoCostoPresentazione as $vocePianoCostoPresentazione) {
            if ($vocePianoCostoPresentazione->getIstruttoria()) {
                $istruttoriaVocePianoCosto = $vocePianoCostoPresentazione->getIstruttoria();
                for ($anno = 1; $anno <= $numeroAnnualita; $anno++) {
                    $sommaImportiAmmessi[$anno] += $istruttoriaVocePianoCosto->{"getImportoAmmissibileAnno".$anno}();
                    $sommaImportiTagliati[$anno] += $istruttoriaVocePianoCosto->{"getTaglioAnno".$anno}();
                }
            }
        }

        for ($anno = 1; $anno <= $numeroAnnualita; $anno++) {
            if ($sommaImportiAmmessi[$anno] == 0.0 && $sommaImportiTagliati[$anno] == 0.0) {
                return false;
            }
        }

        return true;
    }
}
