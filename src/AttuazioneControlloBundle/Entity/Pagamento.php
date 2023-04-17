<?php

namespace AttuazioneControlloBundle\Entity;

use ArrayIterator;
use AttuazioneControlloBundle\Entity\Revoche\Revoca;
use BaseBundle\Entity\StatoIntegrazione;
use BaseBundle\Exception\SfingeException;
use DateInterval;
use DateTime;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Entity\TC39CausalePagamento;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Annotation as Sfinge;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaPagamento;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\Common\Collections\Collection;
use ProtocollazioneBundle\Entity\RichiestaProtocolloPagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\EsitoIstruttoriaPagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento;
use SoggettoBundle\Entity\Soggetto;
use RichiesteBundle\Entity\Proponente;
use AttuazioneControlloBundle\Entity\Istruttoria\AssegnamentoIstruttoriaPagamento;
use AnagraficheBundle\Entity\Personale;
use AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaBando8;
use AttuazioneControlloBundle\Entity\Istruttoria\RipartizioneImportiPagamento;
use DocumentoBundle\Entity\DocumentoFile;
use FascicoloBundle\Entity\IstanzaFascicolo;
use AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento;
use AnagraficheBundle\Entity\Persona;
use CertificazioniBundle\Entity\CertificazionePagamento;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\PagamentoRepository")
 * @ORM\Table(name="pagamenti")
 * @Assert\Callback(callback="validate",groups={"dati_bancari"})
 * @Assert\Callback(callback="validateImportazione",groups={"sanita"})
 */
class Pagamento extends EntityLoggabileCancellabile {

    const GIORNI_ISTRUTTORIA_PAGAMENTO = 90;
    const GIORNI_RISPOSTA_INTEGRAZIONE_DEFAULT = 15;

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta", inversedBy="pagamenti")
     * @ORM\JoinColumn(nullable=false)
     * @var AttuazioneControlloRichiesta
     */
    protected $attuazione_controllo_richiesta;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
     * @ORM\JoinColumn(nullable=true)
     * @var \DocumentoBundle\Entity\DocumentoFile|null
     */
    protected $documento_pagamento;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
     * @ORM\JoinColumn(nullable=true)
     * @var \DocumentoBundle\Entity\DocumentoFile|null
     */
    protected $documento_pagamento_firmato;

    /**
     * @ORM\ManyToOne(targetEntity="AnagraficheBundle\Entity\Persona")
     * @ORM\JoinColumn(nullable=true)
     * @var \AnagraficheBundle\Entity\Persona|null
     */
    protected $firmatario;

    /**
     * @var StatoPagamento|null
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\StatoPagamento")
     * @ORM\JoinColumn(nullable=true)
     * @Sfinge\CampoStato()
     */
    protected $stato;

    /**
     * @var ModalitaPagamento
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\ModalitaPagamento")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $modalita_pagamento;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\DocumentoPagamento", mappedBy="pagamento", cascade={"persist"})
     * @var Collection|DocumentoPagamento[]
     */
    protected $documenti_pagamento;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_invio;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\GiustificativoPagamento", mappedBy="pagamento", cascade={"persist", "remove"})
     * @var Collection|GiustificativoPagamento[]
     */
    protected $giustificativi;

    /**
     * @ORM\OneToOne(targetEntity="FascicoloBundle\Entity\IstanzaFascicolo", cascade={"persist"})
     * @ORM\JoinColumn(name="istanza_fascicolo_id", referencedColumnName="id")
     * @var \FascicoloBundle\Entity\IstanzaFascicolo|null
     */
    protected $istanza_fascicolo;

    /**
     * @ORM\Column(name="banca", type="string", length=1024, nullable=true)
     * @var string|null
     */
    protected $banca;

    /**
     * @ORM\Column(name="intestatario", type="string", length=1024, nullable=true)
     * @var string|null
     */
    protected $intestatario;

    /**
     * @ORM\Column(name="agenzia", type="string", length=1024, nullable=true)
     * @var string|null
     */
    protected $agenzia;

    /**
     * @ORM\Column(name="iban", type="string", length=1024, nullable=true)
     * @Assert\Iban(groups={"dati_generali", "dati_bancari"})
     * @var string|null
     */
    protected $iban;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var boolean|null
     */
    protected $dati_bancari_variati;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     */
    protected $importo_pagamento;

    /**
     * deve essere valorizzato contestualmente all'invio della domanda di pagamento da parte del beneficiario
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     */
    protected $importo_rendicontato;

    /**
     * deve essere valorizzato contestualmente alla validazione della checklist PRINCIPALE
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     */
    protected $importo_rendicontato_ammesso;

    /**
     * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloPagamento", mappedBy="pagamento")
     * @Assert\Valid()
     * @var Collection|RichiestaProtocolloPagamento[]
     */
    protected $richieste_protocollo;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento", mappedBy="pagamento", cascade={"persist"})
     * @var Collection|ValutazioneChecklistPagamento[]
     */
    protected $valutazioni_checklist;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool|null
     */
    protected $esito_istruttoria;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $nota_integrazione;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool|null
     */
    protected $integrazione_sostanziale;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $documento_integrazione;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     */
    protected $importo_richiesto;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="integrato_da")
     * @ORM\JoinColumn(nullable=true)
     * @var Pagamento|null
     */
    protected $integrazione_di;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", mappedBy="integrazione_di")
     * @var Pagamento|null
     */
    protected $integrato_da;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\OperatoreCcPagamento", mappedBy="pagamento", cascade={"persist"})
     * @var Collection|OperatoreCcPagamento[]
     */
    protected $operatori_cc;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_fideiussione;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\MandatoPagamento", inversedBy="pagamento")
     * @ORM\JoinColumn(nullable=true)
     * @var MandatoPagamento|null
     */
    protected $mandato_pagamento;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     */
    protected $importo_certificato;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     */
    protected $importo_decertificato;

    /**
     * @ORM\OneToMany(targetEntity="CertificazioniBundle\Entity\CertificazionePagamento", mappedBy="pagamento", cascade={"persist"})
     * @var Collection|CertificazionePagamento[]
     */
    protected $certificazioni;

    /**
     * @ORM\OneToMany(targetEntity="CertificazioniBundle\Entity\CompensazionePagamento", mappedBy="pagamento", cascade={"persist"})
     * @var Collection|CompensazionePagamento[]
     */
    protected $compensazioni;

    /**
     * @ORM\ManyToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaBando7")
     * @ORM\JoinTable(name="documenti_istruttoria_pagamenti_bando7")
     * @var Collection|\AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaBando7[]
     */
    protected $documenti_istruttoria_bando7;

    /**
     * @ORM\Column(name="data_inizio_rendicontazione", type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_inizio_rendicontazione;

    /**
     * @ORM\Column(name="data_fine_rendicontazione", type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_fine_rendicontazione;

    /**
     * @ORM\Column(name="data_conclusione_progetto", type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_conclusione_progetto;

    /**
     * @var Collection|Personale[]
     * @ORM\OneToMany(targetEntity="AnagraficheBundle\Entity\Personale", mappedBy="pagamento")
     */
    protected $personale;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\ReferentePagamento", mappedBy="pagamento")
     * @var Collection|ReferentePagamento[]
     */
    protected $referenti;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\EstensionePagamento", cascade={"persist"}, inversedBy="pagamento")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $estensione;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\DurcPagamento", mappedBy="pagamento")
     */
    protected $durc;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\AssegnamentoIstruttoriaPagamento", mappedBy="pagamento", cascade={"persist"})
     * @ORM\OrderBy({"dataAssegnamento" = "DESC"})
     * @var Collection|AssegnamentoIstruttoriaPagamento[]
     */
    protected $assegnamenti_istruttoria;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_istruttoria;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Contratto", mappedBy="pagamento")
     * @var Collection|Contratto[]
     */
    protected $contratti;

    /**
     * @ORM\Column(name="anno_spesa", type="string", length=4, nullable=true)
     * @var string|null
     */
    protected $anno_spesa;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    protected $abilita_rendicontazione_chiusa;

    /**
     * @ORM\Column(name="banca_variata", type="string", length=1024, nullable=true)
     * @var string|null
     */
    protected $banca_variata;

    /**
     * @ORM\Column(name="intestatario_variato", type="string", length=1024, nullable=true)
     * @var string|null
     */
    protected $intestatario_variato;

    /**
     * @ORM\Column(name="agenzia_variata", type="string", length=1024, nullable=true)
     * @var string|null
     */
    protected $agenzia_variata;

    /**
     * @ORM\Column(name="iban_variato", type="string", length=1024, nullable=true)
     * @Assert\Iban(groups={"dati_generali", "dati_bancari"})
     * @var string|null
     */
    protected $iban_variato;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_variazione_dati_bancari;

    /**
     * @ORM\Column(name="protocollo_invio_comunicazione_variazione", type="string", length=1024, nullable=true)
     * @var string|null
     */
    protected $protocollo_invio_comunicazione_variazione;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_invio_comunicazione_variazione;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\ObiettivoRealizzativoPagamento", mappedBy="pagamento", cascade={"persist"})
     * @var Collection|ObiettivoRealizzativoPagamento
     */
    protected $obiettivi_realizzativi;

    /*     * ************************ */
    /*     * * PER ISTRUTTORIA 773 ** */
    /*     * ************************ */

    // ATTENZIONE: L'istruttoria 773 è stata richiesta su ogni singola entità (p.es: per ogni documento) MA ANCHE COME CUMULATIVA (p.es: intera sezione documenti)
    // queste colonne implementano l'istruttoria "cumulativa"

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @var IstruttoriaOggettoPagamento|null
     */
    protected $istruttoria_monitoraggio;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @var IstruttoriaOggettoPagamento|null
     */
    protected $istruttoria_dati_bancari;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @var IstruttoriaOggettoPagamento|null
     */
    protected $istruttoria_antimafia;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @var IstruttoriaOggettoPagamento|null
     */
    protected $istruttoria_doc_personali;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @var IstruttoriaOggettoPagamento|null
     */
    protected $istruttoria_doc_amministrativi;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @var IstruttoriaOggettoPagamento|null
     */
    protected $istruttoria_doc_generali;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @var IstruttoriaOggettoPagamento|null
     */
    protected $istruttoria_documenti_progetto;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @var IstruttoriaOggettoPagamento|null
     */
    protected $istruttoria_incremento_occupazionale;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @var IstruttoriaOggettoPagamento|null
     */
    protected $istruttoria_relazione_finale_saldo;

    /*     * ***************************** */
    /*     * * FINE PER ISTRUTTORIA 773 ** */
    /*     * ***************************** */

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento", mappedBy="pagamento")
     * @var Collection|\AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento[]
     */
    protected $integrazioni;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento", mappedBy="pagamento")
     * @var Collection|\AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento[]
     */
    protected $richieste_chiarimenti;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento", mappedBy="pagamento")
     * @var Collection|\AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento[]
     */
    protected $comunicazioni;

    /*
     * campo di appoggio che contiene il contatore per la modalità di pagamento, usato nell'elenco operazioni
     */
    protected $contatore;

    /**
     * @ORM\Column(name="conto_tesoreria", type="string", length=255, nullable=true)
     * @var string|null
     */
    protected $conto_tesoreria;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\EsitoIstruttoriaPagamento", mappedBy="pagamento")
     * @var Collection|EsitoIstruttoriaPagamento[]
     */
    protected $esiti_istruttoria_pagamento;

    /**
     * @ORM\ManyToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaBando8")
     * @ORM\JoinTable(name="documenti_istruttoria_pagamenti_bando8")
     * @var Collection|\AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaBando8[]
     */
    protected $documenti_istruttoria_bando8;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\RipartizioneImportiPagamento", mappedBy="pagamento", cascade={"persist"})
     * @var Collection|\AttuazioneControlloBundle\Entity\Istruttoria\RipartizioneImportiPagamento[]
     */
    protected $ripartizioni_importi_pagamento;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\DiCui", mappedBy="pagamento_provenienza", cascade={"persist"})
     * @var Collection|DiCui[]
     */
    protected $di_cui_provenienza;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\DiCui", mappedBy="pagamento_destinazione", cascade={"persist"})
     * @var Collection|DiCui[]
     */
    protected $di_cui_destinazione;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool|null
     */
    protected $accettazione_autodichiarazioni_autorizzazioni;
    //--variabili di appoggio per form date progetto (sola lettura)
    public $data_avvio_progetto;
    public $data_termine_progetto;
    //-------------------------------------

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\RipartizioneImportiPagamentoBeneficiario", mappedBy="pagamento", cascade={"persist"})
     * @var Collection|RipartizioneImportiPagamentoBeneficiario[]
     */
    protected $ripartizioni_importi_pagamento_beneficiario;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\IncrementoOccupazionale", mappedBy="pagamento", cascade={"persist"})
     * @var Collection|IncrementoOccupazionale[]
     */
    protected $incremento_occupazionale;

    /**
     * @ORM\Column(name="conto_tesoreria_variato", type="string", length=255, nullable=true)
     * @var string|null
     */
    protected $conto_tesoreria_variato;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaPagamento", mappedBy="pagamento", cascade={"persist"})
     * @var Collection|\AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaPagamento[]
     */
    protected $documenti_istruttoria;

    /**
     * deve essere valorizzato contestualmente alla validazione della checklist relativa al post controllo in loco
     * (quindi solo se è previsto un controllo in loco per quel progetto)
     *
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     */
    protected $importo_rendicontato_ammesso_post_controllo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|NULL
     */
    public $data_fine_rendicontazione_forzata;

    /**
     * inserito dall'istruttore in avanzamento, un giorno forse autocalcolato dal sistema :D
     *
     * @ORM\Column(name="contributo_complessivo_spettante", type="decimal", precision=14, scale=2, nullable=true)
     */
    protected $contributo_complessivo_spettante;

    /**
     * Attributo di appoggio per visualizzare eventulamente il valore di importo definito in checklist
     *
     */
    protected $importo_erogabile_checklist;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\CollaborazioneEsternaImpresa", mappedBy="pagamento", cascade={"persist"})
     * @var Collection|CollaborazioneEsternaImpresa[]
     */
    protected $collaborazioni_esterne_imprese;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\VariazionePianoCosti", inversedBy="pagamento")
     * @ORM\JoinColumn(nullable=true)
     *
     * Aggiungiamo questa relazione per gestire le eventuali eccezioni per l'efficacia di una variazione rispetto al pagamento
     * se il pagameto ha una variazione associata allora definirà il piano costi bypassando tutti i controlli.
     * Andrebbe gestita come OneToOne ma purtroppo doctrine le gestisce male e rallenta il caricamento.
     * L'unica differenza a db è un vincolo di UNIQUE diciamo che è un compromesso non proprio piacevole
     */
    protected $variazione;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $data_prima_validazioneck;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $data_convenzione;

    public function __construct(AttuazioneControlloRichiesta $atc = null) {
        $this->documenti_pagamento = new ArrayCollection();
        $this->giustificativi = new ArrayCollection();
        $this->valutazioni_checklist = new ArrayCollection();
        $this->personale = new ArrayCollection();
        $this->referenti = new ArrayCollection();
        $this->contratti = new ArrayCollection();
        $this->integrazioni = new ArrayCollection();
        $this->richieste_chiarimenti = new ArrayCollection();
        $this->esiti_istruttoria_pagamento = new ArrayCollection();
        $this->ripartizioni_importi_pagamento = new ArrayCollection();
        $this->ripartizioni_importi_pagamento_beneficiario = new ArrayCollection();
        $this->incremento_occupazionale = new ArrayCollection();
        $this->di_cui_provenienza = new ArrayCollection();
        $this->di_cui_destinazione = new ArrayCollection();
        $this->documenti_istruttoria = new ArrayCollection();
        $this->collaborazioni_esterne_imprese = new ArrayCollection();
        $this->richieste_protocollo = new ArrayCollection();
        $this->certificazioni = new ArrayCollection();
        $this->operatori_cc = new ArrayCollection();
        $this->documenti_istruttoria_bando7 = new ArrayCollection();
        $this->assegnamenti_istruttoria = new ArrayCollection();
        $this->obiettivi_realizzativi = new ArrayCollection();
        $this->comunicazioni = new ArrayCollection();
        $this->attuazione_controllo_richiesta = $atc;
    }

    public function getId() {
        return $this->id;
    }

    public function getAttuazioneControlloRichiesta(): ?AttuazioneControlloRichiesta {
        return $this->attuazione_controllo_richiesta;
    }

    public function getDocumentoPagamento() {
        return $this->documento_pagamento;
    }

    public function getDocumentoPagamentoFirmato() {
        return $this->documento_pagamento_firmato;
    }

    public function getFirmatario() {
        return $this->firmatario;
    }

    public function getStato(): ?StatoPagamento {
        return $this->stato;
    }

    public function getModalitaPagamento(): ?ModalitaPagamento {
        return $this->modalita_pagamento;
    }

    public function getDocumentiPagamento(): Collection {
        return $this->documenti_pagamento;
    }

    public function getDataInvio(): ?\DateTime {
        return $this->data_invio;
    }

    /** Ritorno solo i giustificativi che hanno a null il giustificativo di origine,
     * perchè in questo caso so che sono giustificativi REALI e non generati da DI CUI
     *
     * @return Collection|GiustificativoPagamento[]
     */
    public function getGiustificativi(): Collection {
        return $this->giustificativi->filter(function ($giustificativo) {
                if (in_array($giustificativo->getPagamento()->getProcedura()->getId(), array(7, 8, 32))) {
                    return \is_null($giustificativo->getGiustificativoOrigine());
                } else {
                    return $this->giustificativi;
                }
            });
    }

    public function getGiustigicativiVisibili() {
        //TIPOLOGIA_SPESE_GENERALI_NASCOSTA
        return $this->giustificativi->filter(function ($giustificativo) {
                if (!in_array($giustificativo->getTipologiaGiustificativo()->getCodice(), array('TIPOLOGIA_SPESE_GENERALI_NASCOSTA'))) {
                    return $this->giustificativi;
                }
            });
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setAttuazioneControlloRichiesta(?AttuazioneControlloRichiesta $attuazione_controllo_richiesta): self {
        $this->attuazione_controllo_richiesta = $attuazione_controllo_richiesta;

        return $this;
    }

    public function setDocumentoPagamento(?DocumentoFile $documento_pagamento): self {
        $this->documento_pagamento = $documento_pagamento;

        return $this;
    }

    public function setDocumentoPagamentoFirmato(?DocumentoFile $documento_pagamento_firmato): self {
        $this->documento_pagamento_firmato = $documento_pagamento_firmato;

        return $this;
    }

    public function setFirmatario(?Persona $firmatario): self {
        $this->firmatario = $firmatario;

        return $this;
    }

    public function setStato(?StatoPagamento $stato): self {
        $this->stato = $stato;

        return $this;
    }

    public function setModalitaPagamento(?ModalitaPagamento $modalita_pagamento): self {
        $this->modalita_pagamento = $modalita_pagamento;

        return $this;
    }

    public function setDocumentiPagamento(Collection $documenti_pagamento): self {
        $this->documenti_pagamento = $documenti_pagamento;

        return $this;
    }

    public function setDataInvio(?\DateTime $data_invio) {
        $this->data_invio = $data_invio;
    }

    public function setGiustificativi(Collection $giustificativi) {
        $this->giustificativi = $giustificativi;
    }

    public function getIstanzaFascicolo(): ?IstanzaFascicolo {
        return $this->istanza_fascicolo;
    }

    public function setIstanzaFascicolo(?IstanzaFascicolo $istanza_fascicolo): self {
        $this->istanza_fascicolo = $istanza_fascicolo;

        return $this;
    }

    public function getBanca(): ?string {
        return $this->banca;
    }

    public function getAgenzia(): ?string {
        return $this->agenzia;
    }

    public function getIban(): ?string {
        return $this->iban;
    }

    public function setBanca(?string $banca): self {
        $this->banca = $banca;

        return $this;
    }

    public function setAgenzia(?string $agenzia): self {
        $this->agenzia = $agenzia;

        return $this;
    }

    public function setIban(?string $iban): self {
        $this->iban = $iban;

        return $this;
    }

    public function getImportoPagamento() {
        return $this->importo_pagamento;
    }

    public function setImportoPagamento($importo_pagamento) {
        $this->importo_pagamento = $importo_pagamento;
    }

    public function getRichiesteProtocollo(): Collection {
        return $this->richieste_protocollo;
    }

    public function setRichiesteProtocollo(Collection $richieste_protocollo): self {
        $this->richieste_protocollo = $richieste_protocollo;

        return $this;
    }

    public function getRichiesta(): Richiesta {
        return $this->attuazione_controllo_richiesta->getRichiesta();
    }

    public function getSoggetto(): ?Soggetto {
        return $this->getRichiesta()->getSoggetto();
    }

    public function getProcedura(): Procedura {
        return $this->getRichiesta()->getProcedura();
    }

    public function getCodiceAsse(): ?string {
        return $this->getProcedura()->getAsse()->getCodice();
    }

    public function getValutazioniChecklist(): Collection {
        return $this->valutazioni_checklist;
    }

    public function setValutazioniChecklist(Collection $valutazioni_checklist): self {
        $this->valutazioni_checklist = $valutazioni_checklist;

        return $this;
    }

    public function addValutazioneChecklist(ValutazioneChecklistPagamento $valutazione_checklist): self {
        $this->valutazioni_checklist->add($valutazione_checklist);
        $valutazione_checklist->setPagamento($this);

        return $this;
    }

    public function isRichiestaDisabilitata(): bool {
        return $this->isPagamentoDisabilitato();
    }

    public function isPagamentoDisabilitato(): bool {
        $stato = $this->getStato()->getCodice();

        return $stato != StatoPagamento::PAG_INSERITO;
    }

    public function addDocumentiPagamento(DocumentoPagamento $documentiPagamento): self {
        $this->documenti_pagamento[] = $documentiPagamento;

        return $this;
    }

    public function removeDocumentiPagamento(DocumentoPagamento $documentiPagamento): void {
        $this->documenti_pagamento->removeElement($documentiPagamento);
    }

    public function addGiustificativi(GiustificativoPagamento $giustificativi): self {
        $this->giustificativi[] = $giustificativi;

        return $this;
    }

    public function removeGiustificativi(GiustificativoPagamento $giustificativi): void {
        $this->giustificativi->removeElement($giustificativi);
    }

    public function addRichiesteProtocollo(RichiestaProtocolloPagamento $richiesteProtocollo): self {
        $this->richieste_protocollo[] = $richiesteProtocollo;

        return $this;
    }

    public function removeRichiesteProtocollo(RichiestaProtocolloPagamento $richiesteProtocollo): void {
        $this->richieste_protocollo->removeElement($richiesteProtocollo);
    }

    public function setIntestatario(?string $intestatario): self {
        $this->intestatario = $intestatario;

        return $this;
    }

    public function getIntestatario(): ?string {
        return $this->intestatario;
    }

    public function addOperatoriCc(OperatoreCcPagamento $operatoriCc): self {
        $this->operatori_cc[] = $operatoriCc;

        return $this;
    }

    public function removeOperatoriCc(OperatoreCcPagamento $operatoriCc): void {
        $this->operatori_cc->removeElement($operatoriCc);
    }

    public function getOperatoriCc(): Collection {
        return $this->operatori_cc;
    }

    public function setDataFideiussione(?\DateTime $dataFideiussione): self {
        $this->data_fideiussione = $dataFideiussione;

        return $this;
    }

    public function getDataFideiussione(): ?\DateTime {
        return $this->data_fideiussione;
    }

    public function getEsitoIstruttoria() {
        return $this->esito_istruttoria;
    }

    public function setEsitoIstruttoria(?bool $esito_istruttoria): self {
        $this->esito_istruttoria = $esito_istruttoria;
        return $this;
    }

    public function getNotaIntegrazione() {
        return $this->nota_integrazione;
    }

    public function setNotaIntegrazione($nota_integrazione) {
        $this->nota_integrazione = $nota_integrazione;
        return $this;
    }

    public function hasIntegrazione(): bool {
        if (!is_null($this->nota_integrazione)) {
            return true;
        }

        if ($this->hasIntegrazioneGiustificativi()) {
            return true;
        }

        if ($this->hasIntegrazioneDocumenti()) {
            return true;
        }

        return false;
    }

    public function hasIntegrazioneGiustificativi(): bool {
        foreach ($this->getGiustificativi() as $giustificativo) {
            if ($giustificativo->getIntegrazione()) {
                return true;
            }
        }

        return false;
    }

    public function hasIntegrazioneDocumenti(): bool {
        foreach ($this->documenti_pagamento as $documento) {
            if ($documento->getIntegrazione()) {
                return true;
            }
        }

        return false;
    }

    function getIntegrazioneSostanziale(): ?bool {
        return $this->integrazione_sostanziale;
    }

    function setIntegrazioneSostanziale(?bool $integrazione_sostanziale) {
        $this->integrazione_sostanziale = $integrazione_sostanziale;
        return $this;
    }

    function getImportoRichiesto() {
        return $this->importo_richiesto;
    }

    function setImportoRichiesto($importo_richiesto): self {
        $this->importo_richiesto = $importo_richiesto;

        return $this;
    }

    function getDocumentoIntegrazione() {
        return $this->documento_integrazione;
    }

    function setDocumentoIntegrazione($documento_integrazione) {
        $this->documento_integrazione = $documento_integrazione;
        return $this;
    }

    function getIntegrazioneDi(): ?Pagamento {
        return $this->integrazione_di;
    }

    function getIntegratoDa(): ?Pagamento {
        return $this->integrato_da;
    }

    function setIntegrazioneDi(?Pagamento $integrazione_di): self {
        $this->integrazione_di = $integrazione_di;

        return $this;
    }

    function setIntegratoDa(?Pagamento $integrato_da): self {
        $this->integrato_da = $integrato_da;

        return $this;
    }

    function getMandatoPagamento(): ?MandatoPagamento {
        return $this->mandato_pagamento;
    }

    function setMandatoPagamento(?MandatoPagamento $mandato_pagamento): self {
        $this->mandato_pagamento = $mandato_pagamento;
        $mandato_pagamento->setPagamento($this);

        return $this;
    }

    function setOperatoriCc(Collection $operatori_cc): self {
        $this->operatori_cc = $operatori_cc;

        return $this;
    }

    /*
     * L'importo ritornato si riferisce alle sole certificazioni approvate,
     * per le quali tale pagamento è stato certificato con un importo positivo.
     * 
     * In caso il pagamento è stato certificato con più certificazioni,
     * questo importo è la somma degli importi certificati nelle varie certificazioni
     */

    public function getImportoCertificato() {
        return $this->importo_certificato;
    }

    public function setImportoCertificato($importo_certificato) {
        $this->importo_certificato = $importo_certificato;
        return $this;
    }

    public function getImportoDecertificato() {
        return $this->importo_decertificato;
    }

    public function setImportoDecertificato($importo_decertificato) {
        $this->importo_decertificato = $importo_decertificato;
        return $this;
    }

    public function getDocumentiIstruttoria(): Collection {
        return $this->documenti_istruttoria;
    }

    public function setDocumentiIstruttoria(Collection $documenti_istruttoria): self {
        $this->documenti_istruttoria = $documenti_istruttoria;

        return $this;
    }

    public function getDocumentiIstruttoriaBando7(): Collection {
        return $this->documenti_istruttoria_bando7;
    }

    public function setDocumentiIstruttoriaBando7(Collection $documenti_istruttoria_bando7): self {
        $this->documenti_istruttoria_bando7 = $documenti_istruttoria_bando7;

        return $this;
    }

    public function getDurc() {
        return $this->durc;
    }

    public function setDurc($durc) {
        $this->durc = $durc;
    }

    /**
     * questa funzione viene richiamata in cascata ogni volta che si crea/modifica/cancella un imputazione su un giustificativo
     * o quando si cancella l'intero giustificativo
     *
     * prima valorizzavo solo importo richiesto, oggi 21/02/2018 d'accordo con le esigenze di vincenzo valorizzo anche la variabile da lui introdotta, importo rendicontato
     * in pratica rappresentano la stessa cosa..
     * in futuro si potrebbe pensare di rimuovere importo richiesto e tenere solo importo rendicontato
     */
    public function calcolaImportoRichiesto() {

        $importo_richiesto = 0;
        foreach ($this->getGiustificativi() as $giustificativo) {
            $importo_richiesto += $giustificativo->getImportoRichiesto();
        }

        $this->setImportoRichiesto($importo_richiesto);
        $this->setImportoRendicontato($importo_richiesto);
    }

    public function getRendicontato() {
        $importo = 0;
        foreach ($this->getGiustificativi() as $giustificativo) {
            $importo += $giustificativo->getImportoRichiesto();
        }
        return $importo;
    }

    public function getRendicontatoAmmesso() {
        $importo = 0;
        foreach ($this->getGiustificativi() as $giustificativo) {
            $importo += $giustificativo->getImportoApprovato();
        }
        return $importo;
    }

    public function getRendicontatoAmmesso773RI() {
        $importo = 0;
        foreach ($this->getGiustificativi() as $giustificativo) {
            $importo += $giustificativo->getImportoAmmesso773('RIND');
        }
        return $importo;
    }

    public function getRendicontatoAmmesso773SP() {
        $importo = 0;
        foreach ($this->getGiustificativi() as $giustificativo) {
            $importo += $giustificativo->getImportoAmmesso773('DEVSP');
        }
        return $importo;
    }

    public function getDescrizioneEsito() {

        $stato_pagamento = $this->getStato()->getCodice();
        $certificazioni = $this->getCertificazioni();
        $stato_certificazione = !is_null($certificazioni) && $certificazioni->last() ? $certificazioni->last()->getCertificazione()->getStato()->getCodice() : null;

        if (is_null($this->esito_istruttoria) && in_array($stato_pagamento, array('PAG_INVIATO_PA', 'PAG_PROTOCOLLATO'))) {
            return "In istruttoria";
        } else if (!is_null($this->esito_istruttoria) && ($this->esito_istruttoria == true) && !is_null($stato_certificazione) && ($stato_certificazione != 'CERT_APPROVATA')) {
            return "In certificazione";
        } else if (!is_null($this->esito_istruttoria) && ($this->esito_istruttoria == true) && !is_null($stato_certificazione) && ($stato_certificazione == 'CERT_APPROVATA')) {
            return "Certificato";
        } else if (!is_null($this->esito_istruttoria) && ($this->esito_istruttoria == true) && is_null($stato_certificazione)) {
            return "Ammesso";
        } else if (!is_null($this->esito_istruttoria) && $this->esito_istruttoria == false) {
            return "Non ammesso";
        } else {
            return "-";
        }
    }

    public function isAssistenzaTecnica(): bool {
        return $this->getProcedura()->getCodiceTipoProcedura() == 'ASSISTENZA_TECNICA';
    }

    public function isIngegneriaFinanziaria(): bool {
        return $this->getProcedura()->getCodiceTipoProcedura() == 'INGEGNERIA_FINANZIARIA';
    }

    public function isAcquisizioni(): bool {
        return $this->getProcedura()->getCodiceTipoProcedura() == 'ACQUISIZIONI';
    }

    public function isProceduraParticolare(): bool {
        return $this->isAssistenzaTecnica() || $this->isIngegneriaFinanziaria() || $this->isAcquisizioni();
    }

    public function isEliminabile(): bool {
        return !\in_array($this->getStato()->getCodice(), array("PAG_INVIATO_PA", "PAG_PROTOCOLLATO"));
    }

    public function isRiapribile(): bool {
        if (!$this->getIntegrazioni()->count() && !$this->getRichiesteChiarimenti()->count() && is_null($this->getEsitoIstruttoria()) && is_null($this->getMandatoPagamento()) && $this->getStato() && in_array($this->getStato()->getCodice(), [StatoPagamento::PAG_INVIATO_PA, StatoPagamento::PAG_PROTOCOLLATO])) {
            return true;
        }
        return false;
    }

    public function isInviato(): bool {
        return \in_array($this->getStato()->getCodice(), array(StatoPagamento::PAG_INVIATO_PA, StatoPagamento::PAG_PROTOCOLLATO));
    }

    public function __clone() {
        if ($this->id) {
            parent::__clone();

            if (!is_null($this->documenti_pagamento)) {
                $documenti_pagamento = new ArrayCollection();
                foreach ($this->documenti_pagamento as $documento_pagamento) {
                    $documento_pagamento_clonato = clone $documento_pagamento;
                    if ($documento_pagamento->getIntegrazione()) {
                        $documento_pagamento_clonato->setIntegrazioneDi($documento_pagamento);
                    } else {
                        $documento_pagamento_clonato->setIntegrazioneDi(null);
                    }
                    $documento_pagamento_clonato->setPagamento($this);
                    $documenti_pagamento[] = $documento_pagamento_clonato;
                }
                $this->setDocumentiPagamento($documenti_pagamento);
            }

            $giustificativi = new ArrayCollection();
            foreach ($this->getGiustificativi() as $giustificativo) {
                $giustificativo_clonato = clone $giustificativo;
                if ($giustificativo->getIntegrazione()) {
                    $giustificativo_clonato->setIntegrazioneDi($giustificativo);
                } else {
                    $giustificativo_clonato->setIntegrazioneDi(null);
                }
                $giustificativo_clonato->setPagamento($this);
                $giustificativi[] = $giustificativo_clonato;
            }
            $this->setGiustificativi($giustificativi);

            $operatori = new ArrayCollection();
            foreach ($this->operatori_cc as $operatore) {
                $operatore_clonato = clone $operatore;
                $operatore_clonato->setPagamento($this);
                $operatori[] = $operatore_clonato;
            }
            $this->setOperatoriCc($operatori);

            $documenti_istruttoria = new ArrayCollection();
            foreach ($this->documenti_istruttoria as $documento_istruttoria) {
                $documenti_istruttoria[] = $documento_istruttoria;
            }
            $this->setDocumentiIstruttoria($documenti_istruttoria);

            $documenti_istruttoria_bando7 = new ArrayCollection();
            foreach ($this->documenti_istruttoria_bando7 as $documento_istruttoria_bando7) {
                $documenti_istruttoria_bando7[] = $documento_istruttoria_bando7;
            }
            $this->setDocumentiIstruttoriaBando7($documenti_istruttoria_bando7);

            if (is_object($this->istanza_fascicolo)) {
                $this->istanza_fascicolo = clone $this->istanza_fascicolo;
            }

            $this->setDocumentoPagamento(null);
            $this->setDocumentoPagamentoFirmato(null);
            $this->setDataInvio(null);
            $this->setImportoPagamento(null);
            $this->setRichiesteProtocollo(new ArrayCollection());
            $this->setValutazioniChecklist(new ArrayCollection());
            $this->setDocumentoIntegrazione(null);
            $this->setStato(null);
            $this->setEsitoIstruttoria(null);
            $this->setNotaIntegrazione(null);
            $this->setMandatoPagamento(null);
        }
    }

    public function addDocumentoIstruttoria(DocumentoIstruttoriaPagamento $documentoIstruttoria) {
        $this->documenti_istruttoria->add($documentoIstruttoria);
        $documentoIstruttoria->setPagamento($this);
    }

    public function removeDocumentoIstruttoria(DocumentoIstruttoriaPagamento $documentoIstruttoria): bool {
        return $this->documenti_istruttoria->removeElement($documentoIstruttoria);
    }

    public function addDocumentoIstruttoriaBando7($documento) {
        $this->documenti_istruttoria_bando7->add($documento);
    }

    public function removeDocumentoIstruttoriaBando7($documento): bool {
        return $this->documenti_istruttoria_bando7->removeElement($documento);
    }

    public function addDocumentoIstruttoriaBando8($documento) {
        $this->documenti_istruttoria_bando8->add($documento);
    }

    public function removeDocumentoIstruttoriaBando8($documento): bool {
        return $this->documenti_istruttoria_bando8->removeElement($documento);
    }

    public function getProtocollo(): string {
        $richiesteProt = $this->getRichiesteProtocollo();
        //Viene considerato solo l'elemento iniziale perché
        //al momento risulta l'unico presente
        //TO DO... foreach()
        $ricProt = $richiesteProt[0];
        if (!empty($ricProt) && (!is_null($ricProt->getNum_pg()))) {
            $protocollo = $ricProt->getRegistro_pg() . "/" . $ricProt->getAnno_pg() . "/" . $ricProt->getNum_pg();
            return $protocollo;
        } else {
            return "-";
        }
    }

    public function getDataProtocollo() {

        $richiesteProt = $this->getRichiesteProtocollo();
        //Viene considerato solo l'elemento iniziale perché
        //al momento risulta l'unico presente
        //TO DO... foreach()
        $ricProt = $richiesteProt[0];

        if (is_null($ricProt)) {
            return '-';
        }

        try {
            $dpg = $ricProt->getDataPg();
            return date_format($dpg, "d/m/Y");
        } catch (\Exception $e) {
            return '-';
        }
    }

    public function getContatore() {
        return $this->contatore;
    }

    public function setContatore($contatore) {
        $this->contatore = $contatore;
    }

    public function getNomeClasse(): string {
        return "Pagamento";
    }

    public function getAssegnamentiIstruttoria(): Collection {
        return $this->assegnamenti_istruttoria;
    }

    public function setAssegnamentiIstruttoria(Collection $assegnamenti_istruttoria): self {
        $this->assegnamenti_istruttoria = $assegnamenti_istruttoria;

        return $this;
    }

    public function getDataIstruttoria() {
        return $this->data_istruttoria;
    }

    public function setDataIstruttoria($data_istruttoria) {
        $this->data_istruttoria = $data_istruttoria;
    }

    public function getAssegnamentoIstruttoriaAttivo(): ?AssegnamentoIstruttoriaPagamento {
        $istruttore = $this->assegnamenti_istruttoria->filter(
                function (AssegnamentoIstruttoriaPagamento $assegnamento): bool {
                    return $assegnamento->getAttivo();
                })
            ->last();

        return $istruttore ?: null;
    }

    public function getGiorniIstruttoria() {
        if (is_null($this->data_invio)) {
            return "-";
        }

        $data_fine = is_null($this->data_istruttoria) ? new \DateTime() : $this->data_istruttoria;
        return $this->data_invio->diff($data_fine)->format('%a%');
    }

    public function isModificabile(): bool {
        return \is_null($this->getMandatoPagamento());
    }

    public function getAnnoSpesa() {
        return $this->anno_spesa;
    }

    public function setAnnoSpesa($anno_spesa) {
        $this->anno_spesa = $anno_spesa;
    }

    public function getDataInizioRendicontazione() {
        return $this->data_inizio_rendicontazione;
    }

    public function getDataFineRendicontazione() {
        return $this->data_fine_rendicontazione;
    }

    public function setDataInizioRendicontazione($data_inizio_rendicontazione) {
        $this->data_inizio_rendicontazione = $data_inizio_rendicontazione;
    }

    public function setDataFineRendicontazione($data_fine_rendicontazione) {
        $this->data_fine_rendicontazione = $data_fine_rendicontazione;
    }

    public function getPersonale() {
        return $this->personale;
    }

    public function setPersonale($personale) {
        $this->personale = $personale;
    }

    public function getReferenti() {
        return $this->referenti;
    }

    public function setReferenti($referenti) {
        $this->referenti = $referenti;
    }

    public function getEstensione() {
        return $this->estensione;
    }

    public function setEstensione($estensione) {
        $this->estensione = $estensione;
    }

    public function isDaMaggiorazioneRicercatori773(): bool {
        $oggettiRichiesta = $this->getRichiesta()->getOggettiRichiesta();
        $oggettoRichiesta = $oggettiRichiesta[0];
        $richiestaMaggiorazione = $oggettoRichiesta->hasMaggiorazione10();
        //$maggiorazioneAccettata = $this->getAttuazioneControlloRichiesta()->hasMaggiorazioneAccettata();

        if ($richiestaMaggiorazione == true) {
            return true;
        }
        return false;
    }

    public function isProponibileDaMaggiorazioneRicercatori773(): bool {
        $oggettiRichiesta = $this->getRichiesta()->getOggettiRichiesta();
        $oggettoRichiesta = $oggettiRichiesta[0];
        $richiestaMaggiorazione = $oggettoRichiesta->hasMaggiorazione10();
        $grandeImpresa = $oggettoRichiesta->isGrandeImpresa();
        $maggiorazioneAccettata = $this->getAttuazioneControlloRichiesta()->getMaggiorazioneAccettata();

        if ($richiestaMaggiorazione == true && is_null($maggiorazioneAccettata) && !$grandeImpresa) {
            return true;
        }
        return false;
    }

    public function getGiustificativiTipologia($codice, $unique = false, $proponente = null) {
        $giustificativi = $this->getGiustificativi();
        $giustificativi_tipologia = array();
        if (!is_null($giustificativi)) {
            foreach ($giustificativi as $giustificativo) {
                $tipologia = $giustificativo->getTipologiaGiustificativo();
                if (!is_null($tipologia) && $tipologia->getCodice() == $codice && (is_null($proponente) || $proponente->getId() == $giustificativo->getProponente()->getId())) {
                    if ($unique) {
                        return $giustificativo;
                    } else {
                        $giustificativi_tipologia[] = $giustificativo;
                    }
                }
            }
        }

        if ($unique) {
            return null;
        } else {
            return $giustificativi_tipologia;
        }
    }

    public function getAbilitaRendicontazioneChiusa() {
        return $this->abilita_rendicontazione_chiusa;
    }

    public function setAbilitaRendicontazioneChiusa($abilita_rendicontazione_chiusa) {
        $this->abilita_rendicontazione_chiusa = $abilita_rendicontazione_chiusa;
    }

    function getContratti() {
        return $this->contratti;
    }

    function setContratti($contratti) {
        $this->contratti = $contratti;
    }

    function addContratto(Contratto $contratto) {
        $contratto->setPagamento($this);
        $this->contratti[] = $contratto;
    }

    public function getDatiBancariVariati() {
        return $this->dati_bancari_variati;
    }

    public function setDatiBancariVariati($dati_bancari_variati) {
        $this->dati_bancari_variati = $dati_bancari_variati;
    }

    /**
     * funzione valida soltanto per il 774
     * avrebbe dovuto stare da un'altra parte..o per lo meno avere un nome diverso..
     */
    public function calcolaContributoRichiesto() {
        $matriceContributo = array();
        $matriceContributo["A"]["RI"] = 0.7;
        $matriceContributo["B"]["RI"] = 0.5;
        $matriceContributo["A"]["SP"] = 0.7;
        $matriceContributo["B"]["SP"] = 0.25;
        $matriceContributo["A"]["DI"] = 1;
        $matriceContributo["B"]["DI"] = 1;

        $importoContributo = 0;
        foreach ($this->getGiustificativi() as $giustif) {
            $proponente = $giustif->getProponente();
            if (is_null($proponente) || is_null($proponente->getTipoNaturaLaboratorio())) {
                continue;
            }
            $tipologia = ($proponente->getOrganismoRicerca() || !$proponente->getTipoNaturaLaboratorio()->getAttivitaEconomica()) ? "A" : "B";
            foreach ($giustif->getVociPianoCosto() as $vpc) {
                $pc = substr($vpc->getVocePianoCosto()->getPianoCosto()->getCodice(), 0, 2);
                if (!\is_null($proponente->getOrganismoRicerca()) && $proponente->getOrganismoRicerca() == true) {
                    if ($pc == 'DI') {
                        $importoContributo += (1 * $vpc->getImporto());
                    } else {
                        $importoContributo += (0.7 * $vpc->getImporto());
                    }
                } else {
                    $importoContributo += ($matriceContributo[$tipologia][$pc] * $vpc->getImporto());
                }
            }
        }

        if ($importoContributo > 1000000) {
            $importoContributo = 1000000;
        }

        return round($importoContributo, 2, PHP_ROUND_HALF_UP);
    }

    public function calcolaImportoRendicontato() {

        $importoRendicontato = 0;
        foreach ($this->getGiustificativi() as $giustif) {
            foreach ($giustif->getVociPianoCosto() as $vpc) {
                $importoRendicontato += $vpc->getImporto();
            }
        }

        return $importoRendicontato;
    }
    
    public function calcolaImportoRendicontatoAmmesso() {

        $importoRendicontato = 0;
        foreach ($this->getGiustificativi() as $giustif) {
            foreach ($giustif->getVociPianoCosto() as $vpc) {
                $importoRendicontato += $vpc->getImportoApprovato();
            }
        }

        return $importoRendicontato;
    }

    function getBancaVariata() {
        return $this->banca_variata;
    }

    function getIntestatarioVariato() {
        return $this->intestatario_variato;
    }

    function getAgenziaVariata() {
        return $this->agenzia_variata;
    }

    function getIbanVariato() {
        return $this->iban_variato;
    }

    function setBancaVariata($banca_variata) {
        $this->banca_variata = $banca_variata;
    }

    function setIntestatarioVariato($intestatario_variato) {
        $this->intestatario_variato = $intestatario_variato;
    }

    function setAgenziaVariata($agenzia_variata) {
        $this->agenzia_variata = $agenzia_variata;
    }

    function setIbanVariato($iban_variato) {
        $this->iban_variato = $iban_variato;
    }

    function getDataVariazioneDatiBancari() {
        return $this->data_variazione_dati_bancari;
    }

    function setDataVariazioneDatiBancari($data_variazione_dati_bancari) {
        $this->data_variazione_dati_bancari = $data_variazione_dati_bancari;
    }

    function getProtocolloInvioComunicazioneVariazione() {
        return $this->protocollo_invio_comunicazione_variazione;
    }

    function getDataInvioComunicazioneVariazione() {
        return $this->data_invio_comunicazione_variazione;
    }

    function setProtocolloInvioComunicazioneVariazione($protocollo_invio_comunicazione_variazione) {
        $this->protocollo_invio_comunicazione_variazione = $protocollo_invio_comunicazione_variazione;
    }

    function setDataInvioComunicazioneVariazione($data_invio_comunicazione_variazione) {
        $this->data_invio_comunicazione_variazione = $data_invio_comunicazione_variazione;
    }

    public function validate(ExecutionContextInterface $context) {
        $arraNoValidate = array(8, 32);
        if (\in_array($this->getRichiesta()->getProcedura()->getId(), $arraNoValidate)) {
            return;
        }
        $intestatario = $this->getIntestatario();
        $banca = $this->getBanca();
        $agenzia = $this->getAgenzia();
        $iban = $this->getIban();
        $variati = $this->getDatiBancariVariati();

        if (\is_null($variati)) {
            $context->buildViolation('Indicare se i dati bancari sono variati')
                ->atPath('dati_bancari_variati')
                ->addViolation();
        }

        if (\is_null($intestatario) || strlen($intestatario) == 0) {
            $context->buildViolation('Il campo INTESTATARIO non è compilato')
                ->atPath('intestatario')
                ->addViolation();
        }

        if (\is_null($banca) || strlen($banca) == 0) {
            $context->buildViolation('Il campo BANCA non è compilato')
                ->atPath('banca')
                ->addViolation();
        }

        if (\is_null($agenzia) || strlen($agenzia) == 0) {
            $context->buildViolation('Il campo AGENZIA non è compilato')
                ->atPath('agenzia')
                ->addViolation();
        }

        if (\is_null($iban) || strlen($iban) == 0) {
            $context->buildViolation('Il campo IBAN non è compilato')
                ->atPath('iban')
                ->addViolation();
        }

        if ($this->getDatiBancariVariati()) {
            if (is_null($this->getIntestatarioVariato()) || strlen($this->getIntestatarioVariato()) == 0) {
                $context->buildViolation('Il campo INTESTATARIO nella sezione Dati bancari Variati non è compilato')
                    ->atPath('intestatario_variato')
                    ->addViolation();
            }
            if (\is_null($this->getBancaVariata()) || strlen($this->getBancaVariata()) == 0) {
                $context->buildViolation('Il campo BANCA nella sezione Dati bancari Variati non è compilato')
                    ->atPath('banca_variata')
                    ->addViolation();
            }
            if (\is_null($this->getAgenziaVariata()) || strlen($this->getAgenziaVariata()) == 0) {
                $context->buildViolation('Il campo AGENZIA nella sezione Dati bancari Variati non è compilato')
                    ->atPath('agenzia_variata')
                    ->addViolation();
            }
            if (\is_null($this->getIbanVariato()) || strlen($this->getIbanVariato()) == 0) {
                $context->buildViolation('Il campo IBAN nella sezione Dati bancari Variati non è compilato')
                    ->atPath('iban_variato')
                    ->addViolation();
            }

            if (\is_null($this->getDataInvioComunicazioneVariazione())) {
                $context->buildViolation('Il campo Data variazione del conto corrente nella sezione Dati bancari Variati non è compilato')
                    ->atPath('data_invio_comunicazione_variazione')
                    ->addViolation();
            }
        }
    }

    public function validateImportazione(ExecutionContextInterface $context) {
        if (\is_null($this->getImportoRichiesto())) {
            $context->buildViolation('importo_richiesto non valorizzato')
                ->atPath('importo_richiesto')
                ->addViolation();
        }

        if (\is_null($this->getDataInizioRendicontazione())) {
            $context->buildViolation('data_inizio_rendicontazione non valorizzato')
                ->atPath('data_inizio_rendicontazione')
                ->addViolation();
        }

        if (\is_null($this->getDataFineRendicontazione())) {
            $context->buildViolation('data_fine_rendicontazione non valorizzato')
                ->atPath('data_fine_rendicontazione')
                ->addViolation();
        }

        if (\is_null($this->getDataConvenzione())) {
            $context->buildViolation('data_convenzione non valorizzato')
                ->atPath('data_convenzione')
                ->addViolation();
        }

        if (\is_null($this->getImportoRendicontato())) {
            $context->buildViolation('importo_rendicontato non valorizzato')
                ->atPath('importo_rendicontato')
                ->addViolation();
        }
    }

    public function getTipologieDocumentiPagamento() {
        $documenti_pagamento = $this->getDocumentiPagamento();
        $documenti_tipologia = array();
        if (!is_null($documenti_pagamento)) {
            foreach ($documenti_pagamento as $documento) {
                $tipologia = $documento->getDocumentoFile()->getTipologiaDocumento()->getDescrizione();
                if (!is_null($tipologia) && !($documento->getDocumentoFile()->getTipologiaDocumento()->getTipologia() == "rendicontazione_dichiarazioni") && !in_array($tipologia, $documenti_tipologia)) {
                    $documenti_tipologia [] = $tipologia;
                }
            }
        }

        return $documenti_tipologia;
    }

    public function getTipologieDocumentiDichiarazioniRendicontazione() {
        $documenti_dichiarazioni = $this->getDocumentiPagamento();
        $documenti_tipologia = array();
        if (!is_null($documenti_dichiarazioni)) {
            foreach ($documenti_dichiarazioni as $documento) {
                $tipologia = $documento->getDocumentoFile()->getTipologiaDocumento()->getDescrizione();
                if (!is_null($tipologia) && $documento->getDocumentoFile()->getTipologiaDocumento()->getTipologia() == "rendicontazione_dichiarazioni" && !in_array($tipologia, $documenti_tipologia)) {
                    $documenti_tipologia [] = $tipologia;
                }
            }
        }

        return $documenti_tipologia;
    }

    public function getObiettiviRealizzativi() {
        return $this->obiettivi_realizzativi;
    }

    public function setObiettiviRealizzativi($obiettivi_realizzativi) {
        $this->obiettivi_realizzativi = $obiettivi_realizzativi;
    }

    function getIstruttoriaMonitoraggio() {
        return $this->istruttoria_monitoraggio;
    }

    function getIstruttoriaDatiBancari() {
        return $this->istruttoria_dati_bancari;
    }

    function getIstruttoriaAntimafia() {
        return $this->istruttoria_antimafia;
    }

    function getIstruttoriaDocPersonali() {
        return $this->istruttoria_doc_personali;
    }

    function getIstruttoriaDocAmministrativi() {
        return $this->istruttoria_doc_amministrativi;
    }

    function setIstruttoriaMonitoraggio($istruttoria_monitoraggio) {
        $this->istruttoria_monitoraggio = $istruttoria_monitoraggio;
    }

    function setIstruttoriaDatiBancari($istruttoria_dati_bancari) {
        $this->istruttoria_dati_bancari = $istruttoria_dati_bancari;
    }

    function setIstruttoriaAntimafia($istruttoria_antimafia) {
        $this->istruttoria_antimafia = $istruttoria_antimafia;
    }

    function setIstruttoriaDocPersonali($istruttoria_doc_personali) {
        $this->istruttoria_doc_personali = $istruttoria_doc_personali;
    }

    function setIstruttoriaDocAmministrativi($istruttoria_doc_amministrativi) {
        $this->istruttoria_doc_amministrativi = $istruttoria_doc_amministrativi;
    }

    public function getCertificazioniArray() {
        $result = array();
        foreach ($this->certificazioni as $certificazione) {
            $result[] = $certificazione->getCertificazione()->__toString();
        }

        return $result;
    }

    public function getContoTesoreria() {
        return $this->conto_tesoreria;
    }

    public function setContoTesoreria($conto_tesoreria) {
        $this->conto_tesoreria = $conto_tesoreria;
    }

    function getIstruttoriaDocGenerali() {
        return $this->istruttoria_doc_generali;
    }

    function setIstruttoriaDocGenerali($istruttoria_doc_generali) {
        $this->istruttoria_doc_generali = $istruttoria_doc_generali;
    }

    public function getDocumentiIstruttoriaBando8() {
        return $this->documenti_istruttoria_bando8;
    }

    public function setDocumentiIstruttoriaBando8($documenti_istruttoria_bando8) {
        $this->documenti_istruttoria_bando8 = $documenti_istruttoria_bando8;
        return $this;
    }

    /**
     * @return Collection|\AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento[]
     */
    public function getIntegrazioni(): Collection {
        return $this->integrazioni;
    }

    public function setIntegrazioni($integrazioni) {
        $this->integrazioni = $integrazioni;
    }

    public function addIntegrazione($integrazione) {
        $this->integrazioni->add($integrazione);
        $integrazione->setPagamento($this);
    }

    public function statoIntegrazione() {
        if (count($this->integrazioni) == 0) {
            return "NO_INTEGRAZIONE";
        }
        /*
         * Commentare nel caso si possa inviare solo un'integrazione per volta 
         */
        foreach ($this->integrazioni as $integrazione) {
            if ($integrazione->getStato() == 'INT_INSERITA') {
                return "In integrazione";
            }
            if ($integrazione->getStato() != 'INT_INSERITA' && $integrazione->getRisposta()->getStato() == 'INT_INSERITA') {
                return "In attesa di risposta";
            }
        }
        /*
         * Fine commento
         */
        $ultimaIntegrazione = $this->integrazioni->last();
        if ($ultimaIntegrazione->getStato() != 'INT_INSERITA' && $ultimaIntegrazione->getRisposta()->getStato() != 'INT_INVIATA_PA') {
            return "In attesa di risposta";
        }
        if ($ultimaIntegrazione->getStato() != 'INT_INSERITA' && $ultimaIntegrazione->getRisposta()->getStato() == 'INT_INVIATA_PA') {
            return "Risposta inviata";
        } else {
            return " - ";
        }
    }

    public function getRichiesteChiarimenti() {
        return $this->richieste_chiarimenti;
    }

    public function setRichiesteChiarimenti($richieste_chiarimenti) {
        $this->richieste_chiarimenti = $richieste_chiarimenti;
    }

    /**
     * @return Collection|\AttuazioneControlloBundle\Entity\Istruttoria\EsitoIstruttoriaPagamento[]
     */
    public function getEsitiIstruttoriaPagamento(): Collection {
        return $this->esiti_istruttoria_pagamento;
    }

    public function setEsitiIstruttoriaPagamento(Collection $esiti_istruttoria_pagamento) {
        $this->esiti_istruttoria_pagamento = $esiti_istruttoria_pagamento;
    }

    public function addEsitiIstruttoriaPagamento(EsitoIstruttoriaPagamento $esiti_istruttoria_pagamento) {
        $this->esiti_istruttoria_pagamento->add($esiti_istruttoria_pagamento);
    }

    public function getRipartizioniImportiPagamento() {
        return $this->ripartizioni_importi_pagamento;
    }

    public function setRipartizioniImportiPagamento($ripartizioni_importi_pagamento) {
        $this->ripartizioni_importi_pagamento = $ripartizioni_importi_pagamento;
    }

    public function addRipartizioneImportiPagamento($ripartizione_importi_pagamento) {
        $this->ripartizioni_importi_pagamento->add($ripartizione_importi_pagamento);
    }

    // valutata nel form date progetto della rendicontazione
    public function needDataFineRendicontazioneSal() {
        return $this->modalita_pagamento && $this->modalita_pagamento->isPagamentoIntermedio();
    }

    public function isInviatoPA() {
        return $this->stato->getCodice() == StatoPagamento::PAG_INVIATO_PA;
    }

    public function isProtocollato() {
        return $this->stato->getCodice() == StatoPagamento::PAG_PROTOCOLLATO;
    }

    public function isInviatoRegione() {
        return $this->isInviatoPA() || $this->isProtocollato();
    }

    public function isValidabile() {
        return $this->stato->getCodice() == StatoPagamento::PAG_INSERITO;
    }

    public function getAccettazioneAutodichiarazioniAutorizzazioni() {
        return $this->accettazione_autodichiarazioni_autorizzazioni;
    }

    public function setAccettazioneAutodichiarazioniAutorizzazioni($accettazione_autodichiarazioni_autorizzazioni) {
        $this->accettazione_autodichiarazioni_autorizzazioni = $accettazione_autodichiarazioni_autorizzazioni;
    }

    // ha senso invocarla su pagamenti già salvati a db 

    /**
     *
     * @return null|string
     */
    public function getTempoRendicontazioneRestante() {
        $dataFineRendicontazione = $this->getDataTermineRendicontazione();

        $now = new \DateTime();
        $giorniRimanenti = $now->diff($dataFineRendicontazione);
        if ($giorniRimanenti->invert) {
            $tempoRestante = '0';
        } else {
            $labels = array('d' => 'giorni', 'h' => 'ore', 'i' => 'minuti', 's' => 'secondi');
            if ($giorniRimanenti->days == 1) {
                $labels['d'] = 'giorno';
            }
            if ($giorniRimanenti->h == 1) {
                $labels['h'] = 'ora';
            }
            if ($giorniRimanenti->m == 1) {
                $labels['i'] = 'minuto';
            }
            if ($giorniRimanenti->s == 1) {
                $labels['s'] = 'secondo';
            }

            // dal caso più specifico al più generico
            if ($giorniRimanenti->d == 0 && $giorniRimanenti->h == 0) {
                $tempoRestante = $giorniRimanenti->format("%i {$labels['i']} %s {$labels['s']}");
            } elseif ($giorniRimanenti->days == 0) {
                $tempoRestante = $giorniRimanenti->format("%h {$labels['h']} %i {$labels['i']}");
            } else {
                $tempoRestante = $giorniRimanenti->format("%a {$labels['d']} %h {$labels['h']}");
            }
        }

        return $tempoRestante;
    }

    //torna il totale richiesto ovvero il totale rendicontato ovvero il totale imputato sulle VociPianoCostoGiustificativi
    public function getImportoTotaleRichiesto() {
        $totale = 0.0;
        foreach ($this->getGiustificativi() as $giustificativo) {
            $totale += $giustificativo->getImportoRichiesto();
        }

        return $totale;
    }

    //torna il totale richiesto ammesso ovvero il totale rendicontato ammesso ovvero il totale imputato sulle VociPianoCostoGiustificativi approvato dall'istruttore
    public function getImportoTotaleRichiestoAmmesso() {
        $totale = 0.0;
        foreach ($this->getGiustificativi() as $giustificativo) {
            $totale += $giustificativo->getImportoApprovato();
        }

        return $totale;
    }

    //Forse è inutile però la lascio in caso di pagamenti con istruttorie anomale ...cosa pi cosa meno fa la stessa cosa di getImportoTotaleRichiestoAmmesso
    //ma a partire dalle singole voci spesa di imputazione e non dal giustificativo
    public function calcolaImportoTotaleRichiestoAmmessoDaVoci() {
        $totale = 0.0;
        foreach ($this->getGiustificativi() as $giustificativo) {
            $totale += $giustificativo->calcolaImportoAmmesso();
        }

        return $totale;
    }

    public function getRipartizioniImportiPagamentoBeneficiario() {
        return $this->ripartizioni_importi_pagamento_beneficiario;
    }

    public function setRipartizioniImportiPagamentoBeneficiario($ripartizioni_importi_pagamento_beneficiario) {
        $this->ripartizioni_importi_pagamento_beneficiario = $ripartizioni_importi_pagamento_beneficiario;
    }

    public function addRipartizioneImportiPagamentoBeneficiario($ripartizioni_importi_pagamento_beneficiario) {
        $this->ripartizioni_importi_pagamento_beneficiario->add($ripartizioni_importi_pagamento_beneficiario);
    }

    public function getImportoRendicontato() {
        return $this->importo_rendicontato;
    }

    public function getImportoRendicontatoAmmesso() {
        return $this->importo_rendicontato_ammesso;
    }

    public function setImportoRendicontato($importo_rendicontato) {
        $this->importo_rendicontato = $importo_rendicontato;
    }

    public function setImportoRendicontatoAmmesso($importo_rendicontato_ammesso) {
        $this->importo_rendicontato_ammesso = $importo_rendicontato_ammesso;
    }

    public function getImportoContributoLiquidabileChecklist() {

        $valutazioni_cl = $this->getValutazioniChecklist();

        //$valutazione_cl = $this->getValutazioniChecklist()->last();

        $contrib_liquidabile_checklist = null;

        foreach ($valutazioni_cl as $valutazione_cl) {

            if ($valutazione_cl != false) {
                $valutazione_elementi_cl = $valutazione_cl->getValutazioniElementi();
                foreach ($valutazione_elementi_cl as $v) {
                    if ($v->getElemento()->getDescrizione() == 'Importo contributo liquidabile') {
                        $contrib_liquidabile_checklist = $v->getValore();
                        break;
                    }
                }
            }

            if (!is_null($contrib_liquidabile_checklist))
                break;
        }

        return $contrib_liquidabile_checklist;
    }

    function getIncrementoOccupazionale() {
        return $this->incremento_occupazionale;
    }

    function setIncrementoOccupazionale(Collection $incremento_occupazionale): self {
        $this->incremento_occupazionale = $incremento_occupazionale;

        return $this;
    }

    public function addIncrementoOccupazionale($incremento_occupazionale): self {
        $this->incremento_occupazionale->add($incremento_occupazionale);

        return $this;
    }

    function getDataConclusioneProgetto() {
        return $this->data_conclusione_progetto;
    }

    function setDataConclusioneProgetto($data_conclusione_progetto) {
        $this->data_conclusione_progetto = $data_conclusione_progetto;
    }

    public function setCertificazioni($certificazioni) {
        $this->certificazioni = $certificazioni;
    }

    /**
     * Set conto_tesoreria_variato
     *
     * @param string $contoTesoreriaVariato
     * @return Pagamento
     */
    public function setContoTesoreriaVariato($contoTesoreriaVariato) {
        $this->conto_tesoreria_variato = $contoTesoreriaVariato;

        return $this;
    }

    /**
     * Get conto_tesoreria_variato
     *
     * @return string
     */
    public function getContoTesoreriaVariato() {
        return $this->conto_tesoreria_variato;
    }

    public function addValutazioniChecklist(\AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento $valutazioniChecklist): self {
        $this->valutazioni_checklist[] = $valutazioniChecklist;

        return $this;
    }

    public function removeValutazioniChecklist(\AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento $valutazioniChecklist): void {
        $this->valutazioni_checklist->removeElement($valutazioniChecklist);
    }

    public function addCertificazioni(\CertificazioniBundle\Entity\CertificazionePagamento $certificazioni): self {
        $this->certificazioni[] = $certificazioni;

        return $this;
    }

    public function removeCertificazioni(\CertificazioniBundle\Entity\CertificazionePagamento $certificazioni): void {
        $this->certificazioni->removeElement($certificazioni);
    }

    public function getCertificazioni(): Collection {
        return $this->certificazioni;
    }

    public function addDocumentiIstruttorium(\DocumentoBundle\Entity\DocumentoFile $documentiIstruttoria): self {
        $this->documenti_istruttoria[] = $documentiIstruttoria;

        return $this;
    }

    public function removeDocumentiIstruttorium(\DocumentoBundle\Entity\DocumentoFile $documentiIstruttoria): void {
        $this->documenti_istruttoria->removeElement($documentiIstruttoria);
    }

    public function addDocumentiIstruttoriaBando7(\AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaBando7 $documentiIstruttoriaBando7): self {
        $this->documenti_istruttoria_bando7[] = $documentiIstruttoriaBando7;

        return $this;
    }

    public function removeDocumentiIstruttoriaBando7(\AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaBando7 $documentiIstruttoriaBando7): void {
        $this->documenti_istruttoria_bando7->removeElement($documentiIstruttoriaBando7);
    }

    public function addPersonale(Personale $personale): self {
        $this->personale[] = $personale;

        return $this;
    }

    public function removePersonale(Personale $personale): void {
        $this->personale->removeElement($personale);
    }

    public function addReferenti(ReferentePagamento $referenti): self {
        $this->referenti[] = $referenti;

        return $this;
    }

    public function removeReferenti(ReferentePagamento $referenti): void {
        $this->referenti->removeElement($referenti);
    }

    public function addAssegnamentiIstruttorium(AssegnamentoIstruttoriaPagamento $assegnamentiIstruttoria): self {
        $this->assegnamenti_istruttoria[] = $assegnamentiIstruttoria;

        return $this;
    }

    public function removeAssegnamentiIstruttorium(AssegnamentoIstruttoriaPagamento $assegnamentiIstruttoria): void {
        $this->assegnamenti_istruttoria->removeElement($assegnamentiIstruttoria);
    }

    /**
     * Add contratti
     *
     * @param \AttuazioneControlloBundle\Entity\Contratto $contratti
     * @return Pagamento
     */
    public function addContratti(Contratto $contratti) {
        $this->contratti[] = $contratti;

        return $this;
    }

    public function removeContratti(Contratto $contratti): void {
        $this->contratti->removeElement($contratti);
    }

    public function addObiettiviRealizzativi(ObiettivoRealizzativoPagamento $obiettiviRealizzativi): self {
        $this->obiettivi_realizzativi[] = $obiettiviRealizzativi;

        return $this;
    }

    public function removeObiettiviRealizzativi(ObiettivoRealizzativoPagamento $obiettiviRealizzativi): void {
        $this->obiettivi_realizzativi->removeElement($obiettiviRealizzativi);
    }

    public function addIntegrazioni(IntegrazionePagamento $integrazioni): self {
        $this->integrazioni[] = $integrazioni;

        return $this;
    }

    public function removeIntegrazioni(IntegrazionePagamento $integrazioni): void {
        $this->integrazioni->removeElement($integrazioni);
    }

    public function addRichiesteChiarimenti(RichiestaChiarimento $richiesteChiarimenti): self {
        $this->richieste_chiarimenti[] = $richiesteChiarimenti;

        return $this;
    }

    public function removeRichiesteChiarimenti(RichiestaChiarimento $richiesteChiarimenti): void {
        $this->richieste_chiarimenti->removeElement($richiesteChiarimenti);
    }

    public function removeEsitiIstruttoriaPagamento(EsitoIstruttoriaPagamento $esitiIstruttoriaPagamento): void {
        $this->esiti_istruttoria_pagamento->removeElement($esitiIstruttoriaPagamento);
    }

    public function addDocumentiIstruttoriaBando8(DocumentoIstruttoriaBando8 $documentiIstruttoriaBando8): self {
        $this->documenti_istruttoria_bando8[] = $documentiIstruttoriaBando8;

        return $this;
    }

    public function removeDocumentiIstruttoriaBando8(DocumentoIstruttoriaBando8 $documentiIstruttoriaBando8): void {
        $this->documenti_istruttoria_bando8->removeElement($documentiIstruttoriaBando8);
    }

    public function addRipartizioniImportiPagamento(RipartizioneImportiPagamento $ripartizioniImportiPagamento): self {
        $this->ripartizioni_importi_pagamento[] = $ripartizioniImportiPagamento;

        return $this;
    }

    public function removeRipartizioniImportiPagamento(RipartizioneImportiPagamento $ripartizioniImportiPagamento): void {
        $this->ripartizioni_importi_pagamento->removeElement($ripartizioniImportiPagamento);
    }

    public function removeIncrementoOccupazionale(IncrementoOccupazionale $incrementoOccupazionale) {
        $this->incremento_occupazionale->removeElement($incrementoOccupazionale);
    }

    function getIstruttoriaIncrementoOccupazionale() {
        return $this->istruttoria_incremento_occupazionale;
    }

    function getIstruttoriaRelazioneFinaleSaldo() {
        return $this->istruttoria_relazione_finale_saldo;
    }

    function setIstruttoriaIncrementoOccupazionale($istruttoria_incremento_occupazionale) {
        $this->istruttoria_incremento_occupazionale = $istruttoria_incremento_occupazionale;
    }

    function setIstruttoriaRelazioneFinaleSaldo($istruttoria_relazione_finale_saldo) {
        $this->istruttoria_relazione_finale_saldo = $istruttoria_relazione_finale_saldo;
    }

    function getDiCuiProvenienza() {
        return $this->di_cui_provenienza;
    }

    function getDiCuiDestinazione() {
        return $this->di_cui_destinazione;
    }

    function setDiCuiProvenienza($di_cui_provenienza) {
        $this->di_cui_provenienza = $di_cui_provenienza;
    }

    function setDiCuiDestinazione($di_cui_destinazione) {
        $this->di_cui_destinazione = $di_cui_destinazione;
    }

    function addDiCuiProvenienza($di_cui_provenienza) {
        $this->di_cui_provenienza->add($di_cui_provenienza);
    }

    function addDiCuiDestinazione($di_cui_destinazione) {
        $this->di_cui_destinazione->add($di_cui_destinazione);
    }

//    function isScorrimentoSaldo(){
//        $r = $this->getRichiesta();
//        $sal = false;
//        foreach ($r->getPagamenti() as $p) {
//            $sal = $p->getModalitaPagamento()->getCodice() == ModalitaPagamento::SAL;
//            if($sal) break;
//        }
//        return $sal && ( $this->getModalitaPagamento()->getCodice() == ModalitaPagamento::SALDO_FINALE);
//    }

    public function getIstruttoriaDocumentiProgetto() {
        return $this->istruttoria_documenti_progetto;
    }

    public function setIstruttoriaDocumentiProgetto($istruttoria_documenti_progetto) {
        $this->istruttoria_documenti_progetto = $istruttoria_documenti_progetto;
    }

    // Questa funzione ritorna, oltre ai giustificativi del pagamento, anche i giustificativi eventualmente ribaltati dal SAL precedenti
    // (o più in generale, dai di_cui di cui (scusa il gioco di parole) questo pagamento ($this) risulta destinatario). 
    // Questa funzione ha inoltre cura di modificare gli importi secondo i dati memorizzati nella tabella di_cui
    public function getGiustificativiConDiCui(?Proponente $proponente = null) {

        // PRIMA DI AGGIUNGERE I GIUSTIFICATIVI CON DI CUI MIGRO I CAMPI IMPORTO E IMPORTO APPROVATO DEI GIUSTIFICATIVI "REALI"
        // NEI NUOVI CAMPI IMPORTO DI CUI e IMPORTO APPROVATO DI CUI
        foreach ($this->giustificativi as $g) {
            foreach ($g->getVociPianoCosto() as $v) {
                $v->setImportoDiCui($v->getImporto());
                $v->setImportoApprovatoDiCui($v->getImportoApprovato());
                $v->setImportoNonAmmessoSuperMassimaliDiCui($v->getImportoNonAmmessoPerSuperamentoMassimali());
                $v->setNotaDiCui($v->getNota());
            }
        }

        foreach ($this->di_cui_destinazione as $di_cui) {
            $vdc = $di_cui->getVocePianoCostoGiustificativo();
            $giustificativo_di_cui = $vdc->getGiustificativoPagamento();
            foreach ($giustificativo_di_cui->getVociPianoCosto() as $v) {
                if ($v->getId() == $vdc->getId()) {
                    $v->setImportoDiCui($di_cui->getImporto());
                    $v->setImportoApprovatoDiCui($di_cui->getImportoApprovato());
                    $v->setImportoNonAmmessoSuperMassimaliDiCui($di_cui->getImportoNonAmmessoPerSuperamentoMassimali());
                    $v->setNotaDiCui($di_cui->getNota());
                } else {
                    // Se $v->getId() != $vdc->getId() allora mi cerco questo ID per vedere se è tra altri di_cui
                    // qualora non lo sia posso tranquillamente settarne a zero gli importi! TRATTASI INFATTI DI VOCE SPESA DEL GIUSTIFICATIVO NON RIBALTATA:
                    if (!$this->isVPCInDiCui($v->getId())) {
                        $v->setImportoDiCui(0);
                        $v->setImportoApprovatoDiCui(0);
                        $v->setImportoNonAmmessoSuperMassimaliDiCui(0);
                        $v->setNotaDiCui('');
                    }
                }
            }

            if (!$this->giustificativi->contains($giustificativo_di_cui)) {
                $this->addGiustificativi($giustificativo_di_cui);
            }
        }

        $giustificativiArray = $this->giustificativi->toArray();

        \usort($giustificativiArray, function ($g1, $g2) {            // Ordinamento
            return strcmp($g1->getTipologiaGiustificativo()->getCodice(), $g2->getTipologiaGiustificativo()->getCodice());
        }
        );

        $this->giustificativi = new ArrayCollection($giustificativiArray);

        // Rimuoviamo i giustificativi che presentano valorizzata la colonna giustificativo_di_origine
        return $this->giustificativi->filter(
                function (GiustificativoPagamento $giustificativo) use ($proponente) {
                    return \is_null($giustificativo->getGiustificativoOrigine()) && (
                    \is_null($proponente) ||
                    \is_null($giustificativo->getProponente()) ||
                    $giustificativo->getProponente() == $proponente
                    );
                });
    }

    private function isVPCInDiCui($id_voce_piano_costo) {
        foreach ($this->di_cui_destinazione as $di_cui) {
            $vdc = $di_cui->getVocePianoCostoGiustificativo();
            if ($vdc->getId() == $id_voce_piano_costo)
                return true;
        }
        return false;
    }

    // calcola il totale non ammesso sui vari giustificativi
    public function calcolaImportoNonAmmesso() {

        $importoNonAmmesso = 0;
        foreach ($this->getGiustificativi() as $giustificativo) {
            $importoNonAmmesso += $giustificativo->calcolaImportoNonAmmesso();
        }

        return $importoNonAmmesso;
    }

    // calcola il totale ammesso sui vari giustificativi
    public function calcolaImportoAmmesso() {

        $importoAmmesso = 0;
        foreach ($this->getGiustificativi() as $giustificativo) {
            $importoAmmesso += $giustificativo->getTotaleImputatoApprovato();
        }

        return $importoAmmesso;
    }

    public function hasMandatoPagamento(): bool {
        return !\is_null($this->mandato_pagamento);
    }

    public function isIstruttoriaConclusa(): bool {
        // al momento
        return $this->hasMandatoPagamento();
    }

    public function getImportoRendicontatoAmmessoPostControllo() {
        return $this->importo_rendicontato_ammesso_post_controllo;
    }

    public function setImportoRendicontatoAmmessoPostControllo($importo_rendicontato_ammesso_post_controllo) {
        $this->importo_rendicontato_ammesso_post_controllo = $importo_rendicontato_ammesso_post_controllo;
    }

    public function getContributoComplessivoSpettante() {
        return $this->contributo_complessivo_spettante;
    }

    public function setContributoComplessivoSpettante($contributo_complessivo_spettante) {
        $this->contributo_complessivo_spettante = $contributo_complessivo_spettante;
    }

    public function getPagamento() {
        return $this;
    }

    /**
     * @return boolean
     */
    function isUltimoPagamento() {
        return !\is_null($this->modalita_pagamento) && (
            $this->modalita_pagamento->getCodice() == ModalitaPagamento::UNICA_SOLUZIONE ||
            $this->modalita_pagamento->getCodice() == ModalitaPagamento::SALDO_FINALE
            );
    }

    /**
     * @return boolean true = rendicontazione iniziale
     */
    function isPrimoPagamento(): bool {
        $procedura = $this->attuazione_controllo_richiesta->getRichiesta()->getProcedura();
        $modalitaPagamentoProcedura = $procedura->getModalitaPagamento()->map(function (ModalitaPagamentoProcedura $mpp) {
            return $mpp->getModalitaPagamento();
        });
        $primaModalitaPagamentoProcedura = \array_reduce($modalitaPagamentoProcedura->toArray(),
            function (?ModalitaPagamento $carry, ModalitaPagamento $corrente) {
                return \is_null($carry) || $carry->getOrdineCronologico() > $corrente->getOrdineCronologico() ? $corrente : $carry;
            });
        return $this->getModalitaPagamento() == $primaModalitaPagamentoProcedura;
    }

    /**
     * @param Pagamento $pagamentoAttuale
     * @return Pagamento|mixed|null
     */
    function getPagamentoPrecedente(Pagamento $pagamentoAttuale) {
        $pagamenti = $pagamentoAttuale->getAttuazioneControlloRichiesta()->getPagamenti();

        $pagamentoPrecedente = null;
        if ($pagamenti->count() > 0) {
            foreach ($pagamenti as $key => $pagamento) {
                if ($pagamento->getId() == $pagamentoAttuale->getId()) {
                    break;
                }
                $pagamentoPrecedente = $pagamento;
            }
        }

        return $pagamentoPrecedente;
    }

    /**
     * @param Pagamento $pagamento
     */
    function creaGiustificativiConImportiDaRipresentare(Pagamento $pagamento) {
        $giustificativi = new ArrayCollection();
        $importoPagamento = 0.00;
        foreach ($this->getGiustificativi() as $giustificativo) {
            $giustificativoClonato = clone $giustificativo;

            if ($giustificativoClonato->getImportoPagamentoSuccessivo()) {
                $giustificativoClonato->setPagamento($this);
                $giustificativoClonato->setIstruttoriaOggettoPagamento(null);
                $giustificativoClonato->setPagamento($pagamento);
                $giustificativoClonato->setGiustificativoOrigine($giustificativo);
                $giustificativoClonato->setDataCreazione(null);
                $giustificativoClonato->setDataModifica(null);
                $giustificativoClonato->setCreatoDa(null);
                $giustificativoClonato->setModificatoDa(null);
                $giustificativoClonato->setImportoRichiesto($giustificativoClonato->getImportoPagamentoSuccessivo());
                $importoPagamento += $giustificativoClonato->getImportoPagamentoSuccessivo();
                $giustificativoClonato->setImportoApprovato(null);

                foreach ($giustificativoClonato->getVociPianoCosto() as $vocePianoCosto) {
                    if ($vocePianoCosto->getImportoPagamentoSuccessivo()) {
                        $vocePianoCosto->setImporto($vocePianoCosto->getImportoPagamentoSuccessivo());
                        $vocePianoCosto->setImportoPagamentoSuccessivo(null);
                        $vocePianoCosto->setImportoApprovato(null);
                        $vocePianoCosto->setDataCreazione(null);
                        $vocePianoCosto->setDataModifica(null);
                        $vocePianoCosto->setCreatoDa(null);
                        $vocePianoCosto->setModificatoDa(null);
                    } else {
                        $giustificativoClonato->removeVocePianoCosto($vocePianoCosto);
                    }
                }

                foreach ($giustificativoClonato->getQuietanze() as $quietanza) {
                    $quietanza->setDataCreazione(null);
                    $quietanza->setDataModifica(null);
                    $quietanza->setCreatoDa(null);
                    $quietanza->setModificatoDa(null);
                }

                foreach ($giustificativoClonato->getDocumentiGiustificativo() as $documentoGiustificativo) {
                    $documentoGiustificativoClonato = clone $documentoGiustificativo;
                    $documentoGiustificativoClonato->setGiustificativoPagamento($giustificativoClonato);
                    $documentoGiustificativoClonato->setIstruttoriaOggettoPagamento(null);
                    $documentoGiustificativoClonato->setDataCreazione(null);
                    $documentoGiustificativoClonato->setDataModifica(null);
                    $documentoGiustificativoClonato->setCreatoDa(null);
                    $documentoGiustificativoClonato->setModificatoDa(null);
                }

                $giustificativi[] = $giustificativoClonato;
            }
        }

        if (!$giustificativi->isEmpty()) {
            $pagamento->setGiustificativi($giustificativi);
            $pagamento->setImportoRichiesto($importoPagamento);
            $pagamento->setImportoRendicontato($importoPagamento);
        }
    }

    /**
     * @param Pagamento $pagamento
     * Funzione ridefinita per essere usata da command che aggiorna 
     * i giustificativi ripresentati in un pagamento già creato
     */
    function aggiornaGiustificativiConImportiDaRipresentare(Pagamento $pagamento, $arrayEsclusi) {
        $importoPagamento = 0.00;
        foreach ($this->getGiustificativi() as $giustificativo) {
            if (!in_array($giustificativo->getId(),$arrayEsclusi)) {
                $giustificativoClonato = clone $giustificativo;

                if ($giustificativoClonato->getImportoPagamentoSuccessivo()) {
                    $giustificativoClonato->setPagamento($this);
                    $giustificativoClonato->setIstruttoriaOggettoPagamento(null);
                    $giustificativoClonato->setPagamento($pagamento);
                    $giustificativoClonato->setGiustificativoOrigine($giustificativo);
                    $giustificativoClonato->setDataCreazione(null);
                    $giustificativoClonato->setDataModifica(null);
                    $giustificativoClonato->setCreatoDa(null);
                    $giustificativoClonato->setModificatoDa(null);
                    $giustificativoClonato->setImportoRichiesto($giustificativoClonato->getImportoPagamentoSuccessivo());
                    $importoPagamento += $giustificativoClonato->getImportoPagamentoSuccessivo();
                    $giustificativoClonato->setImportoApprovato(null);

                    foreach ($giustificativoClonato->getVociPianoCosto() as $vocePianoCosto) {
                        if ($vocePianoCosto->getImportoPagamentoSuccessivo()) {
                            $vocePianoCosto->setImporto($vocePianoCosto->getImportoPagamentoSuccessivo());
                            $vocePianoCosto->setImportoPagamentoSuccessivo(null);
                            $vocePianoCosto->setImportoApprovato(null);
                            $vocePianoCosto->setDataCreazione(null);
                            $vocePianoCosto->setDataModifica(null);
                            $vocePianoCosto->setCreatoDa(null);
                            $vocePianoCosto->setModificatoDa(null);
                        } else {
                            $giustificativoClonato->removeVocePianoCosto($vocePianoCosto);
                        }
                    }

                    foreach ($giustificativoClonato->getQuietanze() as $quietanza) {
                        $quietanza->setDataCreazione(null);
                        $quietanza->setDataModifica(null);
                        $quietanza->setCreatoDa(null);
                        $quietanza->setModificatoDa(null);
                    }

                    foreach ($giustificativoClonato->getDocumentiGiustificativo() as $documentoGiustificativo) {
                        $documentoGiustificativoClonato = clone $documentoGiustificativo;
                        $documentoGiustificativoClonato->setGiustificativoPagamento($giustificativoClonato);
                        $documentoGiustificativoClonato->setIstruttoriaOggettoPagamento(null);
                        $documentoGiustificativoClonato->setDataCreazione(null);
                        $documentoGiustificativoClonato->setDataModifica(null);
                        $documentoGiustificativoClonato->setCreatoDa(null);
                        $documentoGiustificativoClonato->setModificatoDa(null);
                    }

                    $pagamento->addGiustificativi($giustificativoClonato);
                }
            }
        }
        
    }

    function getCollaborazioniEsterneImprese(): Collection {
        return $this->collaborazioni_esterne_imprese;
    }

    function setCollaborazioniEsterneImprese(Collection $collaborazioni_esterne_imprese): self {
        $this->collaborazioni_esterne_imprese = $collaborazioni_esterne_imprese;

        return $this;
    }

    public function addCollaborazioneEsternaImpresa($collaborazione_esterna_impresa): self {
        $this->collaborazioni_esterne_imprese->add($collaborazione_esterna_impresa);
        $collaborazione_esterna_impresa->setPagamento($this);

        return $this;
    }

    public function removeCollaborazioniEsterneImprese(CollaborazioneEsternaImpresa $collaborazione_esterna_impresa): void {
        $this->collaborazioni_esterne_imprese->removeElement($collaborazione_esterna_impresa);
    }

    public function valutazioneChkPagemento($tipo) {
        foreach ($this->valutazioni_checklist as $valutazione) {
            if ($valutazione->getChecklist()->getTipologia() == $tipo) {
                return $valutazione;
            }
        }
        return null;
    }

    public function isInoltrabile(): bool {
        $dataInoltro = ($this->getProcedura()->getDataInoltroPagamento()) ?? (new \DateTime());

        return $dataInoltro <= new \DateTime();
    }

    public function getVariazione() {
        return $this->variazione;
    }

    public function setVariazione($variazione) {
        $this->variazione = $variazione;
    }

    public function getDataPrimaValidazioneck() {
        return $this->data_prima_validazioneck;
    }

    public function setDataPrimaValidazioneck($data_prima_validazioneck) {
        $this->data_prima_validazioneck = $data_prima_validazioneck;
    }

    public function getImportoErogabileChecklist() {
        return $this->importo_erogabile_checklist;
    }

    public function setImportoErogabileChecklist($importo_erogabile_checklist) {
        $this->importo_erogabile_checklist = $importo_erogabile_checklist;
    }

    public function setDataFineRendicontazioneForzata(?\DateTime $dataFineRendicontazioneForzata): self {
        $this->data_fine_rendicontazione_forzata = $dataFineRendicontazioneForzata;

        return $this;
    }

    public function getDataFineRendicontazioneForzata(): ?\DateTime {
        return $this->data_fine_rendicontazione_forzata;
    }

    public function removeDiCuiProvenienza(DiCui $diCuiProvenienza) {
        $this->di_cui_provenienza->removeElement($diCuiProvenienza);
    }

    public function removeDiCuiDestinazione(DiCui $diCuiDestinazione) {
        $this->di_cui_destinazione->removeElement($diCuiDestinazione);
    }

    public function addRipartizioniImportiPagamentoBeneficiario(RipartizioneImportiPagamentoBeneficiario $ripartizioniImportiPagamentoBeneficiario): self {
        $this->ripartizioni_importi_pagamento_beneficiario[] = $ripartizioniImportiPagamentoBeneficiario;

        return $this;
    }

    public function removeRipartizioniImportiPagamentoBeneficiario(RipartizioneImportiPagamentoBeneficiario $ripartizioniImportiPagamentoBeneficiario) {
        $this->ripartizioni_importi_pagamento_beneficiario->removeElement($ripartizioniImportiPagamentoBeneficiario);
    }

    public function addCollaborazioniEsterneImprese(CollaborazioneEsternaImpresa $collaborazioniEsterneImprese): self {
        $this->collaborazioni_esterne_imprese[] = $collaborazioniEsterneImprese;

        return $this;
    }

    public function getModalitaPagamentoProcedura(): ?ModalitaPagamentoProcedura {
        $mod = $this->modalita_pagamento;
        return $this->getProcedura()->getModalitaPagamento()->filter(function (ModalitaPagamentoProcedura $m) use ($mod) {
                $finestraRichiesta = $this->getRichiesta()->getFinestraTemporale() ?: 0;
                $finestraModalita = $m->getFinestraTemporale();
                $isFinestraOk = ($finestraModalita ?? $finestraRichiesta) == $finestraRichiesta;
                $isCodiceUguale = $m->getModalitaPagamento()->getCodice() == $mod->getCodice();

                return $isCodiceUguale && $isFinestraOk;
            })->last() ?: NULL;
    }

    public function getDataTermineRendicontazione(): ?\DateTime {
        if (!\is_null($this->data_fine_rendicontazione_forzata)) {
            return $this->data_fine_rendicontazione_forzata;
        }

        $proroga = $this->getProrogaRendicontazione();
        if ($proroga) {
            $dataScadenzaDate = clone $proroga->getDataScadenza();
            //$dataScadenzaDate->modify('+23 hours')->modify('+59 minutes')->modify('+59 seconds');
            return $dataScadenzaDate;
        }

        $modalitaPagamentoProcedura = $this->getModalitaPagamentoProcedura();
        if (\is_null($modalitaPagamentoProcedura)) {
            throw new \Exception('Modalita pagamento per la procedura non definito');
        }
        return $modalitaPagamentoProcedura->getDataFineRendicontazione();
    }

    public function isRendicontazioneAttiva(): bool {
        $dataInizioRendicontazione = $this->getDataAvvioRendicontazione();
        $dataFineRendicontazione = $this->getDataTermineRendicontazione();
        if (\is_null($dataInizioRendicontazione) || \is_null($dataFineRendicontazione)) {
            return false;
        }

        $now = new \DateTime();
        // dobbiamo permettere di rendicontare se siamo dentro l'intervallo di rendicontazione per quella ModalitaPagamentoProcedura,
        // oppure se è stato abilitato lo scorrimento sulla richiesta
        return ($now >= $dataInizioRendicontazione && $now < $dataFineRendicontazione) || $this->getRichiesta()->getAbilitaScorrimento();
    }

    public function getDataAvvioRendicontazione(): ?\DateTimeInterface {
        if ($this->data_inizio_rendicontazione) {
            return $this->data_inizio_rendicontazione;
        }

        $prorogaRendicontazione = $this->getProrogaRendicontazione();
        if ($prorogaRendicontazione) {
            $dataInizio = $prorogaRendicontazione->getDataInizio();
            return $dataInizio;
        }

        $modalitaProcedura = $this->getModalitaPagamentoProcedura();
        if (\is_null($modalitaProcedura)) {
            throw new \Exception('Modalita pagamento per la procedura non definito');
        }
        $inizio = $modalitaProcedura->getDataInizioRendicontazione();

        return $inizio;
    }

    public function getContributoErogato(): float {
        if ($this->hasMandatoPagamento()) {
            return (float) ($this->mandato_pagamento->getImportoPagato() ?: 0.0);
        }
        if ($this->getRichiesta()->getIstruttoria()->isSoggettoPubblico())
            return $this->getImportoContributoLiquidabileChecklist() ?: 0.0;

        return 0.0;
    }

    /**
     * @return Collection|\AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento[]
     */
    public function getComunicazioni(): Collection {
        return $this->comunicazioni;
    }

    public function setComunicazioni($comunicazioni) {
        $this->comunicazioni = $comunicazioni;
    }

    /**
     * @return int|mixed|string
     * @throws \Exception
     */
    public function getGiorniContatore() {
        $dataProtocolloPagamento = null;
        $richiesteProtocolloPagamento = $this->getRichiesteProtocollo();
        if ($richiesteProtocolloPagamento) {
            $richiestaProtocolloPagamento = $richiesteProtocolloPagamento->last();
            if ($richiestaProtocolloPagamento && $richiestaProtocolloPagamento->getData_pg()) {
                $dataProtocolloPagamento = $richiestaProtocolloPagamento->getData_pg();
                $dataProtocolloPagamento->setTime(0, 0, 0);
            }
        }

        /*
          Non calcoliamo i giorni in caso di:
          - procedura particolare;
          - presenza di revoca;
          - istruttoria negativa;
          - assenza del protocollo del pagamento (non so se può succedere, ma per sicurezza lo metto)
         */
        if ($this->isProceduraParticolare() || $this->isRevocato() || $this->getEsitoIstruttoria() === false || !$dataProtocolloPagamento) {
            return '-';
        }

        // Cerco eventuali integrazioni
        $integrazioniPagamento = $this->getIntegrazioni()->filter(
            function (IntegrazionePagamento $integrazionePagamento) {
                if ($integrazionePagamento->getStato() == StatoIntegrazione::INT_PROTOCOLLATA) {
                    return $integrazionePagamento;
                }
            });

        /** @var IntegrazionePagamento $ultimaIntegrazionePagamento */
        $ultimaIntegrazionePagamento = $integrazioniPagamento->last();

        // Con comunicazione di integrazione
        if ($ultimaIntegrazionePagamento) {
            $ultimaDataIntegrazionePagamento = $this->getUltimaDataComunicazione($ultimaIntegrazionePagamento);

            // Con mandato di pagamento
            if ($this->getMandatoPagamento()) {
                // In caso di mandato di pagamento calcolo i giorni intercorsi tra la data di risposta all'ultima comunicazione di integrazione
                // e la data del mandato di pagamento e li sottraggo da Pagamento::GIORNI_ISTRUTTORIA_PAGAMENTO.
                $dataMandatoPagamento = $this->getMandatoPagamento()->getDataMandato();
                $dataMandatoPagamento->setTime(0, 0, 0);
                $differenza = $ultimaDataIntegrazionePagamento->diff($dataMandatoPagamento);
            } else {
                // Senza mandato di pagamento
                // Se il tempo per rispondere alla comunicazione di integrazione è scaduto vado a contare i giorni dalla data in cui scadeva ad oggi.
                if ($ultimaDataIntegrazionePagamento < new DateTime()) {
                    $differenza = $ultimaDataIntegrazionePagamento->diff(new DateTime());
                } else {
                    // Se la data è futura (il beneficiario è ancora in tempo per rispondere alla comunicazione di integrazione)
                    // conto i giorni dall'invio del pagamento alla data di invio della comunicazione di integrazione.
                    $differenza = $dataProtocolloPagamento->diff($ultimaIntegrazionePagamento->getRichiesteProtocollo()->last()->getData_pg());
                }
            }
        } else {
            // Senza comunicazione di integrazione
            // Con mandato di pagamento
            if ($this->getMandatoPagamento()) {
                // In caso di mandato di pagamento calcolo i giorni intercorsi tra la data di protocollo del pagamento
                // e la data del mandato di pagamento e li sottraggo da Pagamento::GIORNI_ISTRUTTORIA_PAGAMENTO.
                // Data mandato di pagamento
                $dataMandatoPagamento = $this->getMandatoPagamento()->getDataMandato();
                $dataMandatoPagamento->setTime(0, 0, 0);
                $differenza = $dataProtocolloPagamento->diff($dataMandatoPagamento);
            } else {
                // Senza mandato di pagamento
                $differenza = $dataProtocolloPagamento->diff(new DateTime());
            }
        }

        return Pagamento::GIORNI_ISTRUTTORIA_PAGAMENTO - $differenza->days;
    }

    /**
     * @return array|bool
     * @throws \Exception
     */
    public function getUltimaDataPagamento() {
        $richiesteProtocolloPagamento = $this->getRichiesteProtocollo();
        if ($richiesteProtocolloPagamento) {
            // Nel caso in cui fossero presenti più protocolli (non capita praticamente mai) prendo l'ultimo.
            /** @var RichiestaProtocolloPagamento $richiestaProtocolloPagamento */
            $richiestaProtocolloPagamento = $richiesteProtocolloPagamento->last();
            if ($richiestaProtocolloPagamento == false) {
                return false;
            }
            $dataProtocolloPagamento = $richiestaProtocolloPagamento->getData_pg();
            if ($dataProtocolloPagamento) {
                $dataProtocolloPagamento->setTime(0, 0, 0);
            } else {
                return false;
            }
        } else {
            return false;
        }

        $integrazioniPagamento = $this->getIntegrazioni()->filter(
            function (IntegrazionePagamento $integrazionePagamento) {
                if ($integrazionePagamento->getStato() == StatoIntegrazione::INT_PROTOCOLLATA) {
                    return $integrazionePagamento;
                }
            });

        /** @var IntegrazionePagamento $ultimaIntegrazionePagamento */
        $ultimaIntegrazionePagamento = $integrazioniPagamento->last();

        // Se è presente un'integrazione entro qui
        if ($ultimaIntegrazionePagamento) {
            $dataIntegrazionePagamento = $this->getUltimaDataComunicazione($ultimaIntegrazionePagamento);
            $ultimaData = ['ultimaData' => $dataIntegrazionePagamento['dataComunicazione']];
            $ultimaData['isComunicazioneRisposta'] = $dataIntegrazionePagamento['isComunicazioneRisposta'];
        } else {
            // Istruttoria del pagamento in corso senza integrazioni o comunicazioni:
            // calcolo il tempo trascorso dalla data di protocollo del pagamento alla data corrente.
            $ultimaData = ['ultimaData' => $dataProtocolloPagamento, 'isComunicazioneRisposta' => true];
        }

        return $ultimaData;
    }

    /**
     * @param IntegrazionePagamento $integrazionePagamento
     * @return DateTime
     * @throws \Exception
     */
    public function getUltimaDataComunicazione(IntegrazionePagamento $integrazionePagamento) {
        $dataInvioIntegrazionePagamento = clone $integrazionePagamento->getRichiesteProtocollo()->last()->getData_pg();

        $rendicontazioneProceduraConfig = $integrazionePagamento->getPagamento()->getRichiesta()->getProcedura()->getRendicontazioneProceduraConfig();
        $arrayGiorniPerRispostaDaProcedura = $rendicontazioneProceduraConfig ? $rendicontazioneProceduraConfig->getGiorniPerRispostaComunicazioni() : [];

        // Prendo i giorni a disposizione per rispondere.
        // Vado a scalare:
        // - vedo se il dato è presente nella comunicazione stessa;
        // - vedo se il dato è presente a livello di bando (bando - tipologia di pagamento)
        // - prendo il valore di default GIORNI_RISPOSTA_INTEGRAZIONE_DEFAULT
        if ($integrazionePagamento->getGiorniPerRisposta()) {
            $giorniPerRisposta = $integrazionePagamento->getGiorniPerRisposta();
        } else {
            if (isset($arrayGiorniPerRispostaDaProcedura[$integrazionePagamento->getPagamento()->getModalitaPagamento()->getCodice()])) {
                $giorniPerRisposta = $arrayGiorniPerRispostaDaProcedura[$integrazionePagamento->getPagamento()->getModalitaPagamento()->getCodice()];
            } else {
                $giorniPerRisposta = self::GIORNI_RISPOSTA_INTEGRAZIONE_DEFAULT;
            }
        }

        $dataLimiteRisposta = $dataInvioIntegrazionePagamento->add(new DateInterval('P' . $giorniPerRisposta . 'D'));
        if ($integrazionePagamento->getRisposta() && ($integrazionePagamento->getRisposta()->getStato() == StatoIntegrazione::INT_INVIATA_PA || $integrazionePagamento->getRisposta()->getStato() == StatoIntegrazione::INT_PROTOCOLLATA)) {

            // Se ho ricevuto una risposta alla comunicazione:
            // - se è stata risposta nei limiti prendo la data di risposta
            // - se è stata risposta oltre i limiti prendo la data ultima per la risposta (data invio comunicazione + gg per riposta)
            $dataRisposta = $integrazionePagamento->getRisposta()->getData();

            if ($dataRisposta <= $dataLimiteRisposta) {
                $dataRetval = $dataRisposta;
            } else {
                $dataRetval = $dataLimiteRisposta;
            }
        } else {
            // Se ho inviato una comunicazione e *non* ho ancora una risposta:
            // - se i termini sono superati: prendo come data la data di invio + gg per la risposta (come se avesse risposto l'ultimo giorno)
            // - se il beneficiario è ancora in tempo per rispondere: prendo la data di invio della comunicazione
            if ($dataLimiteRisposta > new \DateTime()) {
                $dataRetval = $dataInvioIntegrazionePagamento;
            } else {
                $dataRetval = $dataLimiteRisposta;
            }
        }
        $dataRetval->setTime(0, 0, 0);

        return $dataRetval;
    }

    public function isInItruttoria() {
        if (is_null($this->getStato())) {
            return false;
        }
        $stato_pagamento = $this->getStato()->getCodice();
        if (is_null($this->esito_istruttoria) && in_array($stato_pagamento, array('PAG_INVIATO_PA', 'PAG_PROTOCOLLATO'))) {
            return true;
        }
        return false;
    }

    public function isChecklistValidataAmmissibile() {
        $valutazioni_cl = $this->getValutazioniChecklist();
        foreach ($valutazioni_cl as $valutazione_cl) {
            if ($valutazione_cl != false) {
                if ($valutazione_cl->getChecklist()->getTipologia() == 'PRINCIPALE' && $valutazione_cl->getValidata() == true && $valutazione_cl->getAmmissibile() == true) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isChecklistValidataNonAmmissibile() {
        $valutazioni_cl = $this->getValutazioniChecklist();
        foreach ($valutazioni_cl as $valutazione_cl) {
            if ($valutazione_cl != false) {
                if ($valutazione_cl->getChecklist()->getTipologia() == 'PRINCIPALE' && $valutazione_cl->getValidata() == true && $valutazione_cl->getAmmissibile() == false) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isChecklistLocoValidataAmmissibile() {
        $valutazioni_cl = $this->getValutazioniChecklist();
        foreach ($valutazioni_cl as $valutazione_cl) {
            if ($valutazione_cl != false) {
                if ($valutazione_cl->getChecklist()->getTipologia() == 'POST_CONTROLLO_LOCO' && $valutazione_cl->getValidata() == true && $valutazione_cl->getAmmissibile() == true) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isChecklistLocoValidataNonAmmissibile() {
        $valutazioni_cl = $this->getValutazioniChecklist();
        foreach ($valutazioni_cl as $valutazione_cl) {
            if ($valutazione_cl != false) {
                if ($valutazione_cl->getChecklist()->getTipologia() == 'POST_CONTROLLO_LOCO' && $valutazione_cl->getValidata() == true && $valutazione_cl->getAmmissibile() == false) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getStatoIntegrazione() {
        $giorni_scadenza = 15;
        if ($this->getProcedura()->getId() == 7) {
            $giorni_scadenza = 30;
        }
        if (count($this->getIntegrazioni()) == 0) {
            return 'NO_INTEGRAZIONE';
        } else {
            $integrazione = $this->getIntegrazioni()->last();
            if ($integrazione->getStato()->getCodice() == 'INT_INSERITA') {
                return 'NO_INTEGRAZIONE';
            }

            $gg = $integrazione->calcolaGiorniTrascorsi();
            if ($integrazione->isInAttesaRisposta() == true && $gg <= $giorni_scadenza) {
                return 'IN_ATTESA_RISP';
            }
            if ($integrazione->isInAttesaRisposta() == true && $gg > $giorni_scadenza) {
                return 'MANCATA_RISPOSTA';
            }
            if ($integrazione->isInAttesaRisposta() == false) {
                return 'RISPOSTA_INVIATA';
            }
        }
    }

    public function hasRevoca() {
        if (count($this->attuazione_controllo_richiesta->getRevoca()) > 0) {
            return true;
        }
        return false;
    }

    public function getDescrizioneEsitoNuova() {
        $stato = '-';
        $controlloLoco = $this->getRichiesta()->hasCampionamentoLoco();
        if ($this->isInviatoRegione() == false) {
            return $stato;
        }
        //valuto lo stato dell'integrazione 
        $statoIntegrazione = $this->getStatoIntegrazione();
        switch ($statoIntegrazione) {
            case 'NO_INTEGRAZIONE':
            case 'MANCATA_RISPOSTA':
            case 'RISPOSTA_INVIATA':
                $stato = 'In istruttoria';
                break;
            case 'IN_ATTESA_RISP':
                $stato = 'In integrazione';
                break;
        }
        if ($this->isChecklistValidataAmmissibile() == true && $controlloLoco == false) {
            $stato = 'In liquidazione';
        }
        if ($this->isChecklistValidataAmmissibile() == true && $controlloLoco == true && $this->isChecklistLocoValidataAmmissibile() == false) {
            $stato = 'In istruttoria';
        }
        if ($this->isChecklistValidataAmmissibile() == true && $controlloLoco == true && $this->isChecklistLocoValidataAmmissibile() == true) {
            $stato = 'In liquidazione';
        }
        if (!is_null($this->getMandatoPagamento())) {
            $stato = 'Pagato';
        }
        if (!is_null($this->getMandatoPagamento()) && $this->getRichiesta()->ultimaRevoca() != false && $this->getRichiesta()->ultimaRevoca()->getContributoRevocato() == 0.00) {
            $stato = 'Pagato';
        }
        if ($this->isChecklistValidataNonAmmissibile() == true && $controlloLoco == false) {
            $stato = 'In corso di revoca';
        }
        if ($this->isChecklistValidataAmmissibile() == true && $controlloLoco == true && $this->isChecklistLocoValidataNonAmmissibile() == true) {
            $stato = 'In corso di revoca';
        }
        if ($this->hasRevoca() == true && $this->getRichiesta()->ultimaRevoca() != false && $this->getRichiesta()->ultimaRevoca()->getContributoRevocato() != 0.00) {
            $stato = 'Contributo revocato';
        }
        return $stato;
    }

    public function getProrogaRendicontazione(\DateTime $dataRif = null): ?ProrogaRendicontazione {
        return $this->attuazione_controllo_richiesta->getProrogaRendicontazione($this->modalita_pagamento, $dataRif);
    }

    /**
     * @return bool
     */
    public function isRevocato() {
        $attuazioneEControlloRichiesta = $this->getAttuazioneControlloRichiesta();
        if ($attuazioneEControlloRichiesta) {
            /** @var Revoca[] $revoche */
            $revoche = $attuazioneEControlloRichiesta->getRevoca();
            if (count($revoche)) {
                /** @var CertificazionePagamento[] $certificazioni */
                $certificazioni = $this->getCertificazioni();
                foreach ($certificazioni as $certificazione) {
                    if ($certificazione->getCertificazione()->getStato()) {
                        return false;
                    }
                }

                foreach ($revoche as $revoca) {
                    if ($revoca->getAttoRevoca()) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getDataConvenzione() {
        return $this->data_convenzione;
    }

    public function setDataConvenzione($data_convenzione) {
        $this->data_convenzione = $data_convenzione;
    }

    /**
     * @param Pagamento $pagamento
     * @return bool
     * @throws SfingeException
     */
    public function isSpesaRipresentabile() {
        // Se si tratta di un pagamento intermedio permetto la ripresentazione di una spesa
        try {
            return $this->getModalitaPagamento()->getCausale()->getCausalePagamento() == TC39CausalePagamento::PAGAMENTO_INTERMEDIO;
        } catch (\Exception $e) {
            throw new SfingeException("Errore, informazione causale pagamento mancante");
        }
    }

    public function getCompensazioni(): Collection {
        return $this->compensazioni;
    }

    public function setCompensazioni(Collection $compensazioni) {
        $this->compensazioni = $compensazioni;
    }

    public function getImportoProposto() {
        $importo = null;
        foreach ($this->certificazioni as $certificazione) {
            $importo += $certificazione->getImporto();
        }

        return $importo;
    }

    public function getTaglioProposto() {
        $importo = null;
        foreach ($this->certificazioni as $certificazione) {
            $importo += $certificazione->getImportoTaglio();
        }

        return $importo;
    }

    public function getCertificazioniAnniArray() {
        $result = array();
        foreach ($this->certificazioni as $certificazione) {
            $result[] = $certificazione->getCertificazione()->getNumero() . '.' . $certificazione->getCertificazione()->getAnnoContabile();
        }

        return $result;
    }

    public function integrazioneScaduta() {
        $integrazioniPagamento = $this->getIntegrazioni()->filter(
            function (IntegrazionePagamento $integrazionePagamento) {
                if ($integrazionePagamento->getStato() == StatoIntegrazione::INT_PROTOCOLLATA) {
                    return $integrazionePagamento;
                }
            });

        $ultimaIntegrazionePagamento = $integrazioniPagamento->last();

        // Se è presente un'integrazione entro qui
        if ($ultimaIntegrazionePagamento) {
            $ultimaDataIntegrazionePagamento = $this->getUltimaDataComunicazione($ultimaIntegrazionePagamento);
        } else {
            return false;
        }
        return ($ultimaDataIntegrazionePagamento < new DateTime());
    }

    public function isAnticipo() {
        return $this->modalita_pagamento && $this->modalita_pagamento->isAnticipo();
    }

    public function isAntimafiaRichiesta() {
        return $this->attuazione_controllo_richiesta->getContributoConcesso() > 150000;
    }

    public function sommaIncrementiA() {
        $somma = 0.00;
        foreach ($this->incremento_occupazionale as $incremento) {
            $somma += $incremento->getOccupatiInDataA();
        }
        return $somma;
    }

    public function sommaIncrementiB() {
        $somma = 0.00;
        foreach ($this->incremento_occupazionale as $incremento) {
            $somma += $incremento->getOccupatiInDataB();
        }
        return $somma;
    }

    public function isComplessivoEqualGiustificativi() {
        return bccomp($this->importo_richiesto, $this->getRendicontato(), 2) == 0;
    }

}
