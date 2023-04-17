<?php

namespace RichiesteBundle\Entity;

use AnagraficheBundle\Entity\Persona;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use BaseBundle\Entity\StatoRichiesta;
use BaseBundle\Validator\Constraints\ValidaLunghezza;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DocumentoBundle\Entity\DocumentoFile;
use IstruttorieBundle\Entity\AssegnamentoIstruttoriaRichiesta;
use IstruttorieBundle\Entity\DocumentoIstruttoria;
use IstruttorieBundle\Entity\PosizioneImpegno;
use Performer\PayERBundle\Entity\AcquistoMarcaDaBollo;
use RichiesteBundle\Validator\Constraints\ValidaDatiGenerali;
use RichiesteBundle\Validator\Constraints\ValidaDatiMarcaDaBollo;
use RichiesteBundle\Validator\Constraints\ValidaFaseProcedurale;
use SfingeBundle\Entity\Procedura;
use SoggettoBundle\Entity\TipoIncarico;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Annotation as Sfinge;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use SoggettoBundle\Entity\Soggetto;
use AttuazioneControlloBundle\Entity\RichiestaStatoAttuazioneProgetto;
use AttuazioneControlloBundle\Entity\SoggettiCollegati;
use AttuazioneControlloBundle\Entity\StrumentoAttuativo;
use BaseBundle\Exception\SfingeException;
use Doctrine\Common\Collections\Criteria;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\Finanziamento;
use AttuazioneControlloBundle\Entity\Economia;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Entity\RichiestaPagamento;
use AttuazioneControlloBundle\Entity\RichiestaSpesaCertificata;
use MonitoraggioBundle\Entity\LocalizzazioneGeografica;
use MonitoraggioBundle\Entity\VoceSpesa;
use MonitoraggioBundle\Entity\TC10TipoLocalizzazione;
use MonitoraggioBundle\Entity\TC13GruppoVulnerabileProgetto;
use MonitoraggioBundle\Entity\TC44_45IndicatoriOutput;
use IstruttorieBundle\Entity\ComunicazioneProgetto;
use AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto;
use AttuazioneControlloBundle\Entity\IterProgetto;
use MonitoraggioBundle\Entity\TC7ProgettoComplesso;
use MonitoraggioBundle\Entity\TC8GrandeProgetto;
use MonitoraggioBundle\Entity\TC9TipoLivelloIstituzione;
use MonitoraggioBundle\Entity\TC5TipoOperazione;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AuditBundle\Entity\AuditCampione;
use AttuazioneControlloBundle\Entity\ComunicazioneAttuazione;
/**
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\RichiestaRepository")
 * @ORM\Table(name="richieste",
 *  indexes={
 *      @ORM\Index(name="idx_procedura_id", columns={"procedura_id"}),
 * 		@ORM\Index(name="idx_firmatario_id", columns={"firmatario_id"}),
 * 		@ORM\Index(name="idx_stato_id", columns={"stato_id"}),
 * 		@ORM\Index(name="idx_documento_richiesta_id", columns={"documento_richiesta_id"}),
 * 		@ORM\Index(name="idx_documento_richiesta_firmato_id", columns={"documento_richiesta_firmato_id"}),
 * 		@ORM\Index(name="idx_documento_marca_da_bollo_digitale_id", columns={"documento_marca_da_bollo_digitale_id"}),
 * 		@ORM\Index(name="idx_acquisto_marca_da_bollo_id", columns={"acquisto_marca_da_bollo_id"})
 *  })
 * @ValidaDatiGenerali(groups={"dati_generali"})
 * @ValidaDatiMarcaDaBollo(groups={"dati_marca_da_bollo"})
 * @ValidaFaseProcedurale(groups={"fase_procedurale"})
 */
class Richiesta extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="richieste")
	 * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=false)
         * @Assert\NotNull()
	 */
	private $procedura;

	/**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\OggettoRichiesta", mappedBy="richiesta")
	 * @Assert\Valid()
	 */
	private $oggetti_richiesta;

	/**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\DocumentoRichiesta", mappedBy="richiesta")
	 * @Assert\Valid()
     * @var DocumentoRichiesta[]
	 */
	private $documenti_richiesta;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
	 * @ORM\JoinColumn(name="documento_richiesta_id", referencedColumnName="id", nullable=true)
	 */
	private $documento_richiesta;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
	 * @ORM\JoinColumn(name="documento_richiesta_firmato_id", referencedColumnName="id", nullable=true)
	 */
	private $documento_richiesta_firmato;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
     * @ORM\JoinColumn(name="documento_marca_da_bollo_digitale_id", referencedColumnName="id", nullable=true)
     */
    private $documento_marca_da_bollo_digitale;

	/**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\Proponente", mappedBy="richiesta")
     * @var Collection|Proponente[]
	 */
	private $proponenti;

	/**
	 * @ORM\ManyToOne(targetEntity="AnagraficheBundle\Entity\Persona")
	 * @ORM\JoinColumn(name="firmatario_id", referencedColumnName="id", nullable=true)
     * @var Persona
	 */
	private $firmatario;

    /**
     * @ORM\Column(type="string", length=25, name="tipologia_marca_da_bollo", nullable=true)
     */
    protected $tipologia_marca_da_bollo;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="data_marca_da_bollo", type="date", nullable=true)
	 */
	protected $data_marca_da_bollo;

	/**
	 * @var string $numero_marca_da_bollo
	 * @ORM\Column(type="string", length=255,  name="numero_marca_da_bollo", nullable=true)
	 */
	protected $numero_marca_da_bollo;

	/**
	 * @var boolean $esente_marca_da_bollo
	 * @ORM\Column(type="boolean", name="esente_marca_da_bollo", nullable=true)
	 */
	protected $esente_marca_da_bollo;

	/**
	 * @var boolean $esente_marca_da_bollo
	 * @ORM\Column(type="text", name="riferimento_normativo_esenzione", nullable=true)
	 */
	protected $riferimento_normativo_esenzione;

    /**
     * @var AcquistoMarcaDaBollo|null
     *
     * @ORM\OneToOne(targetEntity="Performer\PayERBundle\Entity\AcquistoMarcaDaBollo")
     * @ORM\JoinColumn(name="acquisto_marca_da_bollo_id", referencedColumnName="id", nullable=true)
     */
    protected $acquistoMarcaDaBollo;

    /**
     * @var string|null $numero_marca_da_bollo_digitale
     * @ORM\Column(name="numero_marca_da_bollo_digitale", type="string", length=255, nullable=true, unique=true)
     */
    protected $numero_marca_da_bollo_digitale;

	/**
	 * @ORM\Column(name="titolo", type="text", nullable=true)
     * @ValidaLunghezza(min=5, max=100, groups={"bando_170"})
     * @ValidaLunghezza(min=5, max=150, groups={"bando_71"})
     * @ValidaLunghezza(min=5, max=200, groups={"bando_33", "bando_65"})
     * @ValidaLunghezza(min=5, max=300, groups={"bando_175"})
	 * @ValidaLunghezza(min=5, max=500, groups={"dati_progetto", "bando_5", "bando_98", "bando_99", "bando_123", "bando_129", "bando130", "bando_152", "bando_153", "bando_178"})
	 * @ValidaLunghezza(min=5, max=1000, groups={"bando_95", "bando_167"})
	 * @ValidaLunghezza(min=5, max=1300, groups={"bando_109", "bando_134"})
     * @Assert\NotNull(groups={"bando_60", "bando_64", "procedure_particolari"})
	 */
	private $titolo;

	/**
	 * @ORM\Column(name="abstract", type="text", nullable=true)
	 * @ValidaLunghezza(min=5, max=500, groups={"bando_33", "bando_65"})
     * @ValidaLunghezza(min=5, max=800, groups={"bando_170"})
     * @ValidaLunghezza(min=5, max=1000, groups={"bando_123"})
     * @ValidaLunghezza(min=5, max=1300, groups={"dati_progetto", "bando_99", "bando_178"})
     * @ValidaLunghezza(min=5, max=1500, groups={"bando_95", "bando_167"})
	 * @ValidaLunghezza(min=5, max=2000, groups={"bando_71", "bando_175"})
	 * @ValidaLunghezza(min=5, max=3000, groups={"bando_109", "bando_134"})
	 * @ValidaLunghezza(min=5, max=4000, groups={"bando_98"})
	 * @ValidaLunghezza(min=5, max=5000, groups={"bando_129", "bando130"})
     * @Assert\NotNull(groups={"bando_60", "bando_64", "procedure_particolari"})
	 */
	private $abstract;

	/**
	 * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento", mappedBy="richiesta")
	 * esplicitato perchè le procedure per il recupero della più recente è costruita su questo criterio
	 * @ORM\OrderBy({"id" = "ASC"})
	 * @Assert\Valid()
	 */
	private $richieste_protocollo;

	/**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\VoceFaseProcedurale", mappedBy="richiesta")
	 */
	protected $voci_fase_procedurale;

	/**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\VocePianoCosto", mappedBy="richiesta")
	 */
	protected $voci_piano_costo;

	/**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\VoceModalitaFinanziamento", mappedBy="richiesta")
	 */
	protected $voci_modalita_finanziamento;

	/**
	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\StatoRichiesta")
	 * @ORM\JoinColumn(name="stato_id", referencedColumnName="id", nullable=true)
	 * @Sfinge\CampoStato()
	 */
	private $stato;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_invio;

	/**
	 * @var boolean $requisiti_rating
	 * @ORM\Column(type="boolean", name="requisiti_rating", nullable=true)
	 */
	protected $requisiti_rating;

	/**
	 * @var boolean $rating
	 * @ORM\Column(type="boolean", name="rating", nullable=true)
	 */
	protected $rating;

	/**
	 * @var string $stelle_rating
	 * @ORM\Column(type="integer", name="stelle_rating", nullable=true)
	 * 
	 */
	protected $stelle_rating;

	/**
	 * @var boolean $femminile
	 * @ORM\Column(type="boolean", name="femminile", nullable=true)
	 */
	protected $femminile;

	/**
	 * @var boolean $giovanile
	 * @ORM\Column(type="boolean", name="giovanile", nullable=true)
	 */
	protected $giovanile;

	/**
	 * @var boolean $incremento_occupazionale
	 * @ORM\Column(type="boolean", name="incremento_occupazionale", nullable=true)
	 */
	protected $incremento_occupazionale;

	/**
	 * @var int $numero_dipendenti_attuale
	 * @ORM\Column(type="integer", name="numero_dipendenti_attuale", nullable=true)
	 *
	 */
	protected $numero_dipendenti_attuale;

	/**
	 * @var int $numero_nuove_unita
	 * @ORM\Column(type="integer", name="numero_nuove_unita", nullable=true)
	 *
	 */
	protected $numero_nuove_unita;

	/**
	 * @ORM\Column(name="contributo_richiesta", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $contributo_richiesta;

	/**
     * @var IstruttoriaRichiesta|null
	 * @ORM\OneToOne(targetEntity="IstruttorieBundle\Entity\IstruttoriaRichiesta", mappedBy="richiesta", cascade={"persist"})
	 */
	protected $istruttoria;

	/**
	 * @var boolean $abilita_gestione_bando_chiuso
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $abilita_gestione_bando_chiuso;

	/**

	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_limite_istruttoria;

	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta", mappedBy="richiesta", cascade={"persist"})
	 */
	protected $attuazione_controllo;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $id_sfinge_2013;

	/**
	 * @ORM\OneToMany(targetEntity="AuditBundle\Entity\AuditCampione", mappedBy="richiesta")
	 */
	private $audit_campioni;

	/**
	 * @ORM\OneToMany(targetEntity="AuditBundle\Entity\AuditCampioneOperazione", mappedBy="richiesta")
	 */
	private $audit_campioni_operazioni;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $note_riapertura;

	/**
	 * @var boolean $blocco_variazione
	 * @ORM\Column(type="boolean", name="blocco_variazione", nullable=true)
	 */
	protected $blocco_variazione;

	/**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\ObiettivoRealizzativo", mappedBy="richiesta")
     * @var Collection|ObiettivoRealizzativo[]
	 */
	protected $obiettivi_realizzativi;

	/**
	 * @ORM\ManyToMany(targetEntity="SfingeBundle\Entity\Atto", cascade={"persist"})
	 * @ORM\JoinTable(name="richieste_atti",
	 *      joinColumns={@ORM\JoinColumn(name="richiesta_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="atto_id", referencedColumnName="id")}
	 *      )
     * @var Collection|Atto[]
	 */
	protected $atti;

    /**
     *
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC6TipoAiuto")
     * @ORM\JoinColumn(name="mon_tipo_aiuto", referencedColumnName="id")
     * @var \MonitoraggioBundle\Entity\TC6TipoAiuto
     */
    protected $mon_tipo_aiuto;

    /**
     *
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC5TipoOperazione")
     * @ORM\JoinColumn(name="mon_tipo_operazione", referencedColumnName="id")
     * @var \MonitoraggioBundle\Entity\TC5TipoOperazione
     */
    protected $mon_tipo_operazione;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\RichiestaProgramma", mappedBy="richiesta", cascade={"persist"})
     */
    protected $mon_programmi;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\StrumentoAttuativo", mappedBy="richiesta", cascade={"persist"})
     */
    protected $mon_strumenti_attuativi;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\ProceduraAggiudicazione", mappedBy="richiesta")
     * @var Collection
     */
    protected $mon_procedure_aggiudicazione;

    /**
     *
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC48TipoProceduraAttivazioneOriginaria")
     * @ORM\JoinColumn(name="mon_tipo_tipo_procedura_att_orig_id", referencedColumnName="id")
     * @var \MonitoraggioBundle\Entity\TC48TipoProceduraAttivazioneOriginaria
     * @Assert\NotNull( groups={"monitoraggio"})
     */
    protected $mon_tipo_procedura_att_orig;

    /**
     * @ORM\Column(type = "string", nullable = true, length = 30)
     * @var string
     * @Assert\Length(max=30, maxMessage="Massimo {{ limit }} caratteri", groups={"monitoraggio"})
     */
    protected $mon_cod_procedura_att_orig;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\IterProgetto", mappedBy="richiesta", cascade={"persist", "remove"})
     * @var Collection
     */
    protected $mon_iter_progetti;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\RichiestaStatoAttuazioneProgetto", mappedBy="richiesta", cascade={"persist", "remove"})
     * @var Collection
     */
    protected $mon_stato_progetti;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC7ProgettoComplesso")
     * @ORM\JoinColumn( name="mon_progetto_complesso_id", referencedColumnName="id")
     * @var \MonitoraggioBundle\Entity\TC7ProgettoComplesso
     */
    protected $mon_progetto_complesso;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC8GrandeProgetto")
     * @ORM\JoinColumn( name="mon_grande_progetto_id", referencedColumnName="id")
     * @var \MonitoraggioBundle\Entity\TC8GrandeProgetto
     */
    protected $mon_grande_progetto;

    /**
     * @ORM\Column(type = "boolean", nullable = true)
     */
    protected $mon_generatore_entrate;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC9TipoLivelloIstituzione")
     * @ORM\JoinColumn( name="mon_liv_istituzione_str_fin_id", referencedColumnName="id")
     * @var \MonitoraggioBundle\Entity\TC9TipoLivelloIstituzione
     */
    protected $mon_liv_istituzione_str_fin;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\IndicatoreOutput", mappedBy="richiesta", cascade={"persist", "remove"})
     * @var Collection|IndicatoreOutput[]
     */
    protected $mon_indicatore_output;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\IndicatoreRisultato", mappedBy="richiesta", cascade={"persist", "remove"})
     * @var Collection|IndicatoreRisultato[]
     */
    protected $mon_indicatore_risultato;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $mon_fondo_di_fondi;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\SoggettiCollegati", mappedBy="richiesta", cascade={"persist", "remove"})
     * @var Collection
     */
    protected $mon_soggetti_correlati;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Finanziamento", mappedBy="richiesta", cascade={"persist", "remove"})
     * @var Collection
     */
    protected $mon_finanziamenti;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Economia", mappedBy="richiesta", cascade={"persist", "remove"})
     * @var Collection
     */
    protected $mon_economie;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * serve per diversificare rapidamente le richieste di uno stesso bando appertenenti a diverse finestre temporali
     */
    protected $finestra_temporale;
    
    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\RichiestaImpegni", mappedBy="richiesta", cascade={"persist", "remove"})
     * @var Collection
     * @Assert\Valid
     */
    protected $mon_impegni;
    
    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\RichiestaPagamento", mappedBy="richiesta", cascade={"persist", "remove"})
     * @var Collection
     */
    protected $mon_richieste_pagamento;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\RichiestaSpesaCertificata", mappedBy="richiesta", cascade={"persist", "remove"})
     * @var Collection
     */
    protected $mon_spese_certificate;

    /**
     * @ORM\OneToMany(targetEntity="MonitoraggioBundle\Entity\RichiestaPianoCosti", mappedBy="richiesta", cascade={"persist", "remove"})
     * @var Collection
     */
    protected $mon_piano_costi;

    /**
     * @ORM\OneToMany(targetEntity="MonitoraggioBundle\Entity\VoceSpesa", mappedBy="richiesta", cascade={"persist", "remove"})
     * @var Collection
     */
    protected $mon_voce_spesa;

    /**
     * @ORM\OneToMany(targetEntity="MonitoraggioBundle\Entity\LocalizzazioneGeografica", mappedBy="richiesta", cascade={"persist", "remove"})
     * @var Collection
     */
    protected $mon_localizzazione_geografica;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC10TipoLocalizzazione")
     * @ORM\JoinColumn( name="mon_tipo_localizzazione_id", referencedColumnName="id")
     * @var \MonitoraggioBundle\Entity\TC10TipoLocalizzazione
     */
    protected $mon_tipo_localizzazione;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC13GruppoVulnerabileProgetto")
     * @ORM\JoinColumn( name="mon_gruppo_vulnerabile_id", referencedColumnName="id")
     * @var \MonitoraggioBundle\Entity\TC13GruppoVulnerabileProgetto
     */
    protected $mon_gruppo_vulnerabile;

    /**
	 * @ORM\Column(type="boolean", nullable=true)
	 * viene valutato nell'isrendicontazionescaduta dei pagamenti..per gestire quei casi in cui la rendicontazione è scaduta
	 * e si verifica uno scorrimento..ad oggi sto ipotizzando
	 */
	protected $abilita_scorrimento;
	
	/**
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\ComunicazioneProgetto", mappedBy="richiesta")
	 */
	protected $comunicazioni_progetto;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\ComunicazioneAttuazione", mappedBy="richiesta")
	 */
	protected $comunicazioni_attuazione;
	
	/**
	 * @ORM\OneToMany(targetEntity="CertificazioniBundle\Entity\RegistroDebitori", mappedBy="richiesta")
	 */
	private $registro;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto", mappedBy="richiesta")
	 */
	protected $controlli;
	
	/**
	 * @ORM\OneToMany(targetEntity="SoggettoBundle\Entity\IncaricoPersonaRichiesta", mappedBy="richiesta")
	 */
	protected $incarichi_richiesta;
	
	/**
	 * @var boolean $sede_montana
	 * @ORM\Column(type="boolean", name="sede_montana", nullable=true)
	 */
	protected $sede_montana;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $accettazione_autodichiarazioni;
	
	/**
	 * @ORM\Column(name="nota_fase_procedurale", type="text", nullable=true)
	 * @Assert\Length(min=5, max=2000)
     * @Assert\NotNull(groups={"nota_fase_procedurale"}, message="Compilare campo note")
	 */
	protected $nota_fase_procedurale;
	
	/**
	 * @var boolean $aiuto_stato_progetto
	 * @ORM\Column(type="boolean", name="aiuto_stato_progetto", nullable=true)
	 */
	protected $aiuto_stato_progetto;

	/**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\RisorsaProgetto", mappedBy="richiesta")
	 */
	protected $risorse_progetto;
	
	/**
	 * @var boolean $flag_por
	 * @ORM\Column(type="boolean", options={"default" : true})
	 */
   protected $flag_por;
   
    /**
	 * @var boolean $flag_inviato_monit
	 * @ORM\Column(type="boolean", options={"default" : false})
	 */
   protected $flag_inviato_monit;

    /**
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\AssegnamentoIstruttoriaRichiesta", mappedBy="richiesta", cascade={"persist"})
     * @ORM\OrderBy({"dataAssegnamento" = "DESC"})
     * @var Collection|AssegnamentoIstruttoriaRichiesta[]
     */
    protected $assegnamenti_istruttoria;

    /**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\InterventoSede", mappedBy="richiesta")
     * @var Collection|InterventoSede[]
	 */
    private $intervento_sede;
    
    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $mon_prg_pubblico = false;

    /**
	 * @ORM\OneToMany(targetEntity="Fornitore", mappedBy="richiesta")
     * @var Collection|Fornitore[]
	 */
	private $fornitori;

    /**
	 * @var string $utente_invio
	 * @ORM\Column(type="string", length=255,  name="utente_invio", nullable=true)
	 */
	protected $utente_invio;

    /**
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\DocumentoIstruttoria", mappedBy="richiesta")
     * @Assert\Valid()
     * @var DocumentoIstruttoria[]|Collection
     */
    public $documenti_istruttoria;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\SedeOperativaRichiesta", mappedBy="richiesta")
     * @var Collection|SedeOperativaRichiesta[]
     */
    private $sedi_operative;
    
    /**
     * @ORM\OneToOne(targetEntity="RichiesteBundle\Entity\DichiarazioneDsnh", mappedBy="richiesta", cascade={"persist"})
     * @var DichiarazioneDsnh|null
     */
    private $dichiarazione_dnsh;

    /**
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\PosizioneImpegno", mappedBy="richiesta")
     * @var Collection|PosizioneImpegno[]
     */
    private $posizioni_impegni;
    
    
	function getId() {
		return $this->id;
	}

	function getProcedura(): ?Procedura {
		return $this->procedura;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setProcedura(Procedura $procedura): self {
        $this->procedura = $procedura;
        $this->mon_prg_pubblico = $procedura->getMonTipoBeneficiario() == Procedura::MON_TIPO_PRG_PUBBLICO;

        return $this;
	}

	public function addProponente($proponente) {
		$this->proponenti->add($proponente);
	}

	/**
	 * @return Proponente[]|Collection
	 */
	public function getProponenti(): Collection {
		return $this->proponenti;
	}

	/**
	 * @param mixed $proponenti
     * @return Richiesta
	 */
	public function setProponenti($proponenti) {
        $this->proponenti = $proponenti;
        return $this;
	}

	public function addOggettoRichiesta($oggetto_richiesta) {
		$this->oggetti_richiesta->add($oggetto_richiesta);
	}

	/**
	 * @return Collection
	 */
	public function getOggettiRichiesta(): Collection {
		return $this->oggetti_richiesta;
	}

	/**
	 * @param $oggetti_richiesta
	 */
	public function setOggettiRichiesta($oggetti_richiesta): self {
        $this->oggetti_richiesta = $oggetti_richiesta;

        return $this;
	}

	/**
	 * @return Collection
	 */
	public function getDocumentiRichiesta() {
		return $this->documenti_richiesta;
	}

	/**
	 * @param mixed $documenti_richiesta
     * @return Richiesta
	 */
	public function setDocumentiRichiesta($documenti_richiesta) {
        $this->documenti_richiesta = $documenti_richiesta;
        return $this;
	}

	/**
	 * @return Persona
	 */
	public function getFirmatario() {
		return $this->firmatario;
	}

	/**
	 * @param mixed $firmatario
     * @return Richiesta
	 */
	public function setFirmatario($firmatario) {
        $this->firmatario = $firmatario;
        return $this;
	}

    /**
     * @return mixed
     */
    public function getTipologiaMarcaDaBollo()
    {
        return $this->tipologia_marca_da_bollo;
    }

    /**
     * @param mixed $tipologia_marca_da_bollo
     */
    public function setTipologiaMarcaDaBollo($tipologia_marca_da_bollo): void
    {
        $this->tipologia_marca_da_bollo = $tipologia_marca_da_bollo;
    }

	/**
	 * @return \DateTime
	 */
	public function getDataMarcaDaBollo() {
		return $this->data_marca_da_bollo;
	}

	/**
	 * @param \DateTime $data_marca_da_bollo
     * @return Richiesta
	 */
	public function setDataMarcaDaBollo($data_marca_da_bollo) {
        $this->data_marca_da_bollo = $data_marca_da_bollo;
        return $this;
	}

	/**
	 * @return string
	 */
	public function getNumeroMarcaDaBollo() {
		return $this->numero_marca_da_bollo;
	}

	/**
	 * @param string $numero_marca_da_bollo
     * @return Richiesta
	 */
	public function setNumeroMarcaDaBollo($numero_marca_da_bollo) {
        $this->numero_marca_da_bollo = $numero_marca_da_bollo;
        return $this;
	}

	/**
	 * @return boolean
	 */
	public function isEsenteMarcaDaBollo() {
		return $this->esente_marca_da_bollo;
	}

	/**
	 * @param boolean $esente_marca_da_bollo
     * @return Richiesta
	 */
	public function setEsenteMarcaDaBollo($esente_marca_da_bollo) {
        $this->esente_marca_da_bollo = $esente_marca_da_bollo;
        return $this;
	}

    /**
     * @return mixed
     */
    public function getTitolo() {
        return $this->titolo;
    }

	/**
	 * @param mixed $titolo
     * @return Richiesta
	 */
	public function setTitolo($titolo) {
        $this->titolo = $titolo;
        return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAbstract() {
		return $this->abstract;
	}

	/**
	 * @param mixed $abstract
     * @return Richiesta
	 */
	public function setAbstract($abstract) {
        $this->abstract = $abstract;
        return $this;
	}

	public function getRichiesteProtocollo() {
		return $this->richieste_protocollo;
	}

    /**
     * @return Richiesta
     */
	public function setRichiesteProtocollo($richieste_protocollo) {
        $this->richieste_protocollo = $richieste_protocollo;
        return $this;
	}

    /**
     * @return VoceFaseProcedurale[]|Collection
     */
	public function getVociFaseProcedurale() {
		return $this->voci_fase_procedurale;
	}

    /**
     * @return Richiesta
     */
	public function setVociFaseProcedurale($voci_fase_procedurale) {
        $this->voci_fase_procedurale = $voci_fase_procedurale;
        return $this;
	}

	/**
	 * @return StatoRichiesta
	 */
	public function getStato() {
		return $this->stato;
	}

	/**
	 * @param StatoRichiesta $stato
     * @return Richiesta
	 */
	public function setStato(StatoRichiesta $stato) {
        $this->stato = $stato;
        return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDataInvio() {
		return $this->data_invio;
	}

	/**
	 * @param mixed $data_invio
     * @return Richiesta
	 */
	public function setDataInvio($data_invio) {
        $this->data_invio = $data_invio;
        return $this;
	}

	/**
	 * @return DocumentoFile
	 */
	public function getDocumentoRichiesta() {
		return $this->documento_richiesta;
	}

	/**
	 * @param mixed $documento_richiesta
     * @return Richiesta
	 */
	public function setDocumentoRichiesta($documento_richiesta) {
        $this->documento_richiesta = $documento_richiesta;
        return $this;
	}

	/**
	 * @return DocumentoFile
	 */
	public function getDocumentoRichiestaFirmato() {
		return $this->documento_richiesta_firmato;
	}

	/**
	 * @param mixed $documento_richiesta_firmato
     * @return Richiesta
	 */
	public function setDocumentoRichiestaFirmato($documento_richiesta_firmato) {
        $this->documento_richiesta_firmato = $documento_richiesta_firmato;
        return $this;
	}

    /**
     * @return mixed
     */
    public function getDocumentoMarcaDaBolloDigitale()
    {
        return $this->documento_marca_da_bollo_digitale;
    }

    /**
     * @param mixed $documento_marca_da_bollo_digitale
     */
    public function setDocumentoMarcaDaBolloDigitale($documento_marca_da_bollo_digitale): void
    {
        $this->documento_marca_da_bollo_digitale = $documento_marca_da_bollo_digitale;
    }

    /**
     * @return VocePianoCosto[]|Collection
     */
	public function getVociPianoCosto() {
		return $this->voci_piano_costo;
	}

    /**
     * @return Richiesta
     */
	public function setVociPianoCosto($voci_piano_costo) {
        $this->voci_piano_costo = $voci_piano_costo;
        return $this;
	}

	/**
	 * @return boolean
	 */
	public function isRating() {
		return $this->rating;
	}

	/**
	 * @param boolean $rating
     * @return Richiesta
	 */
	public function setRating($rating) {
        $this->rating = $rating;
        return $this;
	}

	/**
	 * @return boolean
	 */
	public function isFemminile() {
		return $this->femminile;
	}

	/**
	 * @param boolean $femminile
     * @return Richiesta
	 */
	public function setFemminile($femminile) {
		$this->femminile = $femminile;
        return $this;
	}

	/**
	 * @return boolean
	 */
	public function isGiovanile() {
		return $this->giovanile;
	}

	/**
	 * @param boolean $giovanile
     * @return Richiesta
	 */
	public function setGiovanile($giovanile) {
		$this->giovanile = $giovanile;
        return $this;
	}

	/**
	 * @return bool
	 */
	public function isIncrementoOccupazionale(): ?bool
	{
		return $this->incremento_occupazionale;
	}

	/**
	 * @param bool $incremento_occupazionale
	 */
	public function setIncrementoOccupazionale(bool $incremento_occupazionale): void
	{
		$this->incremento_occupazionale = $incremento_occupazionale;
	}

    /**
     * @return int
     */
    public function getNumeroDipendentiAttuale(): ?int
    {
        return $this->numero_dipendenti_attuale;
    }

    /**
     * @param int $numero_dipendenti_attuale
     */
    public function setNumeroDipendentiAttuale(?int $numero_dipendenti_attuale): void
    {
        $this->numero_dipendenti_attuale = $numero_dipendenti_attuale;
    }

    /**
     * @return int
     */
    public function getNumeroNuoveUnita(): ?int
    {
        return $this->numero_nuove_unita;
    }

    /**
     * @param int $numero_nuove_unita
     */
    public function setNumeroNuoveUnita(?int $numero_nuove_unita): void
    {
        $this->numero_nuove_unita = $numero_nuove_unita;
    }

	/**
	 * @return Collection|VoceModalitaFinanziamento[]
	 */
	public function getVociModalitaFinanziamento() {
		return $this->voci_modalita_finanziamento;
	}

    /**
     * @return Richiesta
     */
	public function setVociModalitaFinanziamento($voci_modalita_finanziamento) {
		$this->voci_modalita_finanziamento = $voci_modalita_finanziamento;
        return $this;
	}

    /**
     * @return Soggetto
     */
	public function getSoggetto() {
		$mandatario = $this->getMandatario();

		return is_null($mandatario) ? null : $mandatario->getSoggetto();
	}

    /**
     * @return Proponente
     */
	public function getMandatario() {
		foreach ($this->proponenti as $proponente) {
			if ($proponente->getMandatario()) {
				return $proponente;
			}
		}

		return null;
	}

	public function hasMandatario() {
		if (!is_null($this->getSoggetto()))
			return true;
		else
			return false;
	}

    /**
     * @return ArrayCollection|Collection
     */
	public function getProponentiMandatarioFirst() {
		$criteria = Criteria::create()->orderBy(["mandatario" => Criteria::DESC]);
		return $this->proponenti->matching($criteria);
	}

    /**
     * @return ArrayCollection|Collection
     */
    public function getProponentiMandanti() {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('mandatario', false));
        return $this->proponenti->matching($criteria);
    }
	
	/*
	 * Di solito la richiesta dovrebbe avere una sola richiesta protocollo associata(al più una per tipo), ma può capitare che venga chiesto
	 * (in godfather style) di modificare una pratica già protocollata per la quale andrà generata una nuova richiesta protocollo
	 * ..per cui va presa sempre la richiesta protocollo più recente
	 * 
	 * questo metodo deve fare riferimento solo alle RichiesteProtocolloFinanziamento
	 * 
	 * attenzione: il filtraggio per nomeClasse risulta necessario perchè essendoci eredità in cascata anche se la relazione richieste_protocollo punta ad un tipo specifico
	 * può tornare anche istanze derivate dalla classe specificata
	 */

	public function getProtocollo() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach ($this->richieste_protocollo as $r) {
			if ($r->getNomeClasse() == 'ProtocolloFinanziamento') {
				$richiestaProtocollo = $r;
			}
		}

		$protocollo = '-';
		if (!is_null($richiestaProtocollo)) {
			$protocollo = $richiestaProtocollo->getProtocollo();
		}

		return $protocollo;
	}
    
    public function getFascicoloProtocollo() {

		$richiestaProtocollo = null;
		// in caso di più richieste protocollo mi prendo l'ultima(la più recente)
		foreach ($this->richieste_protocollo as $r) {
			if ($r->getNomeClasse() == 'ProtocolloFinanziamento') {
				$richiestaProtocollo = $r;
			}
		}

		$fascicolo = '-';
		if (!is_null($richiestaProtocollo)) {
			$fascicolo = $richiestaProtocollo->getFascicolo();
		}

		return $fascicolo;
	}
    
    /**
     * Richiesta constructor.
     */
    public function __construct() {
        // parent::__construct();
        $this->oggetti_richiesta = new ArrayCollection();
        $this->proponenti = new ArrayCollection();
        $this->documenti_richiesta = new ArrayCollection();
        $this->voci_fase_procedurale = new ArrayCollection();
        $this->voci_piano_costo = new ArrayCollection();
        $this->voci_modalita_finanziamento = new ArrayCollection();
        $this->richieste_protocollo = new ArrayCollection();
        $this->obiettivi_realizzativi = new ArrayCollection();
        $this->atti = new ArrayCollection();
        $this->mon_programmi = new ArrayCollection();
        $this->mon_strumenti_attuativi = new ArrayCollection();
        $this->mon_procedure_aggiudicazione = new ArrayCollection();
        $this->mon_iter_progetti = new ArrayCollection();
        $this->mon_stato_progetti = new ArrayCollection();
        $this->mon_indicatore_output = new ArrayCollection();
        $this->mon_indicatore_risultato = new ArrayCollection();
        $this->mon_soggetti_correlati = new ArrayCollection();
        $this->mon_finanziamenti = new ArrayCollection();
        $this->mon_economie = new ArrayCollection();
        $this->mon_impegni = new ArrayCollection();
        $this->mon_richieste_pagamento = new ArrayCollection();
        $this->mon_spese_certificate = new ArrayCollection();
        $this->mon_piano_costi = new ArrayCollection();
        $this->mon_localizzazione_geografica = new ArrayCollection();
		$this->comunicazioni_progetto = new ArrayCollection();
		$this->comunicazioni_attuazione = new ArrayCollection();
        $this->controlli = new ArrayCollection();
        $this->mon_voce_spesa = new ArrayCollection();
		$this->risorse_progetto = new ArrayCollection();
        $this->assegnamenti_istruttoria = new ArrayCollection();
        $this->fornitori = new ArrayCollection();
		$this->setFlagInviatoMonit(false);
		$this->setFlagPor(true);
    }

    public function getDataProtocollo() {

        $richiestaProtocollo = null;
        // in caso di più richieste protocollo mi prendo l'ultima(la più recente)
        foreach ($this->richieste_protocollo as $r) {
            if ($r->getNomeClasse() == 'ProtocolloFinanziamento') {
                $richiestaProtocollo = $r;
            }
        }

        $data = null;
        if (!is_null($richiestaProtocollo)) {
            $data = $richiestaProtocollo->getDataPg();
        }

        try {
            $data = date_format($data, "d/m/Y");
        } catch (\Exception $e) {
            return '-';
        }

        return $data;
    }

    public function getProponentiPianoCosto() {
        $proponenti = new ArrayCollection();

        foreach ($this->proponenti as $proponente) {
            if ($proponente->hasPianoCosto()) {
                $proponenti->add($proponente);
            }
        }

        return $proponenti;
    }

    public function isInviata() {
        return in_array($this->getStato()->getCodice(), array(StatoRichiesta::PRE_INVIATA_PA, StatoRichiesta::PRE_PROTOCOLLATA));
    }

    public function getTotaleCertificato() {
        $totale = 0;

        if (!is_null($this->attuazione_controllo)) {
            foreach ($this->attuazione_controllo->getPagamenti() as $pagamento) {
                $totale += $pagamento->getImportoCertificato();
                $totale -= $pagamento->getImportoDecertificato();
            }
        }

        return $totale;
    }

    public function getNomeClasse() {
        return "Richiesta";
    }

    /**
     * @return OggettoRichiesta
     */
    public function getPrimoOggetto() {
        $oggetti = $this->oggetti_richiesta;
        return $oggetti[0];
    }

    public function getVocePianoCostoByCodice($codicePianoCosto) {
        foreach ($this->voci_piano_costo as $voce) {
            if ($voce->getPianoCosto()->getCodice() == $codicePianoCosto) {
                return $voce;
            }
        }
    }

    public function getCostoAmmesso() {

        // le intenzioni erano buone, ma a quanto pare le variazioni potrebbero non avere gli eventuali tagli
        // per cui il valore di costo_ammesso non è attendibile
        // 
        // ho aggiunto il flag ignora variazione per risolvere puntualmente alucni disallineamenti de 774
        // perchè in teoria la variazione dovrebbe avere i valori più aggiornati, ma di fatto in alcuni casi non è così
        // Bisognerebbe implementare una logica che tenga conto della data più recente tra istruttoria e variazione
        // ma non si conoscono le date..lo schifo dello schifo

        /*
         * Ad oggi la logica è che se c'è la variazione si legge dalla variazione, altrimenti si legge da istruttoria.
         * Se per l'ultima variazione è settato il flag di ignora variazione allora leggo da istruttoria ( e ovviamente NON dalla eventuale variazione precedente)
         */
        $atc = $this->getAttuazioneControllo();
        if (!is_null($atc)) {
            $variazione = $atc->getUltimaVariazioneApprovata();
            if (!is_null($variazione) && !$variazione->getIgnoraVariazione()) {
                $costoAmmessoVariato = $variazione->getCostoAmmessoVariato();
                if (!is_null($costoAmmessoVariato)) {
                    return $costoAmmessoVariato;
                }
            }
        }

        $istruttoria = $this->getIstruttoria();
        if (!is_null($istruttoria)) {
            $costoAmmesso = $istruttoria->getCostoAmmesso();
            return !is_null($costoAmmesso) ? $costoAmmesso : 0.00;
        }

        return 0.00;
    }

	public function getRichiesta() {
		return $this;
	}

	/**
	 * Remove voci_fase_procedurale
	 *
	 * @param \RichiesteBundle\Entity\VoceFaseProcedurale $vociFaseProcedurale
	 */
	public function removeVociFaseProcedurale(\RichiesteBundle\Entity\VoceFaseProcedurale $vociFaseProcedurale) {
		$this->voci_fase_procedurale->removeElement($vociFaseProcedurale);
	}

	/**
	 * Add voci_piano_costo
	 *
	 * @param \RichiesteBundle\Entity\VocePianoCosto $vociPianoCosto
	 * @return Richiesta
	 */
	public function addVociPianoCosto(\RichiesteBundle\Entity\VocePianoCosto $vociPianoCosto) {
		$this->voci_piano_costo[] = $vociPianoCosto;

		return $this;
	}

	/**
	 * Remove voci_piano_costo
	 *
	 * @param \RichiesteBundle\Entity\VocePianoCosto $vociPianoCosto
	 */
	public function removeVociPianoCosto(\RichiesteBundle\Entity\VocePianoCosto $vociPianoCosto) {
		$this->voci_piano_costo->removeElement($vociPianoCosto);
	}

	/**
	 * Add voci_modalita_finanziamento
	 *
	 * @param \RichiesteBundle\Entity\VoceModalitaFinanziamento $vociModalitaFinanziamento
	 * @return Richiesta
	 */
	public function addVociModalitaFinanziamento(\RichiesteBundle\Entity\VoceModalitaFinanziamento $vociModalitaFinanziamento) {
		$this->voci_modalita_finanziamento[] = $vociModalitaFinanziamento;
		return $this;
	}

	/**
	 * Remove voci_modalita_finanziamento
	 *
	 * @param \RichiesteBundle\Entity\VoceModalitaFinanziamento $vociModalitaFinanziamento
	 */
	public function removeVociModalitaFinanziamento(\RichiesteBundle\Entity\VoceModalitaFinanziamento $vociModalitaFinanziamento) {
		$this->voci_modalita_finanziamento->removeElement($vociModalitaFinanziamento);
	}

	/**
	 * Set requisiti_rating
	 *
	 * @param boolean $requisitiRating
	 * @return Richiesta
	 */
	public function setRequisitiRating($requisitiRating) {
		$this->requisiti_rating = $requisitiRating;

		return $this;
	}

	/**
	 * Get requisiti_rating
	 *
	 * @return boolean 
	 */
	public function getRequisitiRating() {
		return $this->requisiti_rating;
	}

	public function getIdSfinge2013() {
		return $this->id_sfinge_2013;
	}

    /**
     * return Richiesta
     */
	public function setIdSfinge2013($id_sfinge_2013) {
		$this->id_sfinge_2013 = $id_sfinge_2013;
		return $this;
	}

	public function getAuditCampioni() {
		return $this->audit_campioni;
	}

    /**
     * @return Richiesta
     */
	public function setAuditCampioni($audit_campioni) {
        $this->audit_campioni = $audit_campioni;
        return $this;
	}

	public function getAuditCampioniOperazioni() {
		return $this->audit_campioni_operazioni;
	}

    /**
     * @return Richiesta
     */
    public function setAuditCampioniOperazioni($audit_campioni_operazioni) {
		$this->audit_campioni_operazioni = $audit_campioni_operazioni;
        return $this;
	}

	public function getNoteRiapertura() {
		return $this->note_riapertura;
	}

    /**
     * @return Richiesta
     */
	public function setNoteRiapertura($note_riapertura) {
		$this->note_riapertura = $note_riapertura;
        return $this;
	}

	public function isAssistenzaTecnica() {
		return $this->getProcedura()->getCodiceTipoProcedura() == 'ASSISTENZA_TECNICA' ? true : false;
	}

	public function isIngegneriaFinanziaria() {
		return $this->getProcedura()->getCodiceTipoProcedura() == 'INGEGNERIA_FINANZIARIA' ? true : false;
	}
	
	public function isAcquisizioni() {
		return $this->getProcedura()->getCodiceTipoProcedura() == 'ACQUISIZIONI' ? true : false;
	}

	public function isProceduraParticolare() {
		if ($this->isAssistenzaTecnica() || $this->isIngegneriaFinanziaria() || $this->isAcquisizioni()) {
			return true;
		} else {
			return false;
		}
	}

	public function getBloccoVariazione() {
		return $this->blocco_variazione;
	}

	public function getObiettiviRealizzativi() {
		return $this->obiettivi_realizzativi;
	}

    /**
     * @return Richiesta
     */
	public function setObiettiviRealizzativi($obiettivi_realizzativi) {
		$this->obiettivi_realizzativi = $obiettivi_realizzativi;
        return $this;
	}

	public function getRiferimentoNormativoEsenzione() {
		return $this->riferimento_normativo_esenzione;
	}

    /**
     * @return Richiesta
     */
	public function setRiferimentoNormativoEsenzione($riferimento_normativo_esenzione) {
		$this->riferimento_normativo_esenzione = $riferimento_normativo_esenzione;
        return $this;
	}

    /**
     * @return AcquistoMarcaDaBollo|null
     */
    public function getAcquistoMarcaDaBollo(): ?AcquistoMarcaDaBollo
    {
        return $this->acquistoMarcaDaBollo;
    }

    /**
     * @param AcquistoMarcaDaBollo|null $acquistoMarcaDaBollo
     */
    public function setAcquistoMarcaDaBollo(?AcquistoMarcaDaBollo $acquistoMarcaDaBollo): void
    {
        $this->acquistoMarcaDaBollo = $acquistoMarcaDaBollo;
    }

    /**
     * @return string|null
     */
    public function getNumeroMarcaDaBolloDigitale(): ?string
    {
        return $this->numero_marca_da_bollo_digitale;
    }

    /**
     * @param string|null $numero_marca_da_bollo_digitale
     */
    public function setNumeroMarcaDaBolloDigitale(?string $numero_marca_da_bollo_digitale): void
    {
        $this->numero_marca_da_bollo_digitale = $numero_marca_da_bollo_digitale;
    }

	public function getAtti() {
		return $this->atti;
	}

    /**
     * @return Richiesta
     */
	public function setAtti($atti) {
        $this->atti = $atti;
        return $this;
	}
	
	public function getFinestraTemporale() {
		return $this->finestra_temporale;
	}

    /**
     * @return Richiesta
     */
	public function setFinestraTemporale($finestra_temporale) {
        $this->finestra_temporale = $finestra_temporale;
        return $this;
	}

	public function getAbilitaScorrimento() {
		return $this->abilita_scorrimento;
	}

    /**
     * @return Richiesta
     */
	public function setAbilitaScorrimento($abilita_scorrimento) {
        $this->abilita_scorrimento = $abilita_scorrimento;
        return $this;
	}
	
	public function getComunicazioniProgetto() {
		return $this->comunicazioni_progetto;
    }
    
    /**
     * @return Richiesta
     */
	public function setComunicazioniProgetto($comunicazioni_progetto) {
		$this->comunicazioni_progetto = $comunicazioni_progetto;
        return $this;
	}
	
	public function getComunicazioniAttuazione() {
		return $this->comunicazioni_attuazione;
    }
    
    /**
     * @return Richiesta
     */
	public function setComunicazioniAttuazione($comunicazioni_attuazione) {
		$this->comunicazioni_attuazione = $comunicazioni_attuazione;
        return $this;
	}

    /**
     * Get esente_marca_da_bollo
     *
     * @return boolean 
     */
    public function getEsenteMarcaDaBollo()
    {
        return $this->esente_marca_da_bollo;
    }

    /**
     * Get rating
     *
     * @return boolean 
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set stelle_rating
     *
     * @param integer $stelleRating
     * @return Richiesta
     */
    public function setStelleRating($stelleRating)
    {
        $this->stelle_rating = $stelleRating;

        return $this;
    }

    /**
     * Get stelle_rating
     *
     * @return integer 
     */
    public function getStelleRating()
    {
        return $this->stelle_rating;
    }

    /**
     * Get femminile
     *
     * @return boolean 
     */
    public function getFemminile()
    {
        return $this->femminile;
    }

    /**
     * Get giovanile
     *
     * @return boolean 
     */
    public function getGiovanile()
    {
        return $this->giovanile;
    }

    /**
     * Set contributo_richiesta
     *
     * @param string $contributoRichiesta
     * @return Richiesta
     */
    public function setContributoRichiesta($contributoRichiesta)
    {
        $this->contributo_richiesta = $contributoRichiesta;

        return $this;
    }

    /**
     * Get contributo_richiesta
     *
     * @return string 
     */
    public function getContributoRichiesta()
    {
        return $this->contributo_richiesta;
    }

    /**
     * Set abilita_gestione_bando_chiuso
     *
     * @param boolean $abilitaGestioneBandoChiuso
     * @return Richiesta
     */
    public function setAbilitaGestioneBandoChiuso($abilitaGestioneBandoChiuso)
    {
        $this->abilita_gestione_bando_chiuso = $abilitaGestioneBandoChiuso;

        return $this;
    }

    /**
     * Get abilita_gestione_bando_chiuso
     *
     * @return boolean 
     */
    public function getAbilitaGestioneBandoChiuso()
    {
        return $this->abilita_gestione_bando_chiuso;
    }

    /**
     * Set data_limite_istruttoria
     *
     * @param \DateTime $dataLimiteIstruttoria
     * @return Richiesta
     */
    public function setDataLimiteIstruttoria($dataLimiteIstruttoria)
    {
        $this->data_limite_istruttoria = $dataLimiteIstruttoria;

        return $this;
    }

    /**
     * Get data_limite_istruttoria
     *
     * @return \DateTime 
     */
    public function getDataLimiteIstruttoria()
    {
        return $this->data_limite_istruttoria;
    }

    /**
     * Set blocco_variazione
     *
     * @param boolean $bloccoVariazione
     * @return Richiesta
     */
    public function setBloccoVariazione($bloccoVariazione)
    {
        $this->blocco_variazione = $bloccoVariazione;

        return $this;
    }

    /**
     * Set mon_cod_procedura_att_orig
     *
     * @param string $monCodProceduraAttOrig
     * @return Richiesta
     */
    public function setMonCodProceduraAttOrig($monCodProceduraAttOrig)
    {
        $this->mon_cod_procedura_att_orig = $monCodProceduraAttOrig;

        return $this;
    }

    /**
     * Get mon_cod_procedura_att_orig
     *
     * @return string 
     */
    public function getMonCodProceduraAttOrig()
    {
        return $this->mon_cod_procedura_att_orig;
    }

    /**
     * Set mon_generatore_entrate
     *
     * @param boolean $monGeneratoreEntrate
     * @return Richiesta
     */
    public function setMonGeneratoreEntrate($monGeneratoreEntrate)
    {
        $this->mon_generatore_entrate = $monGeneratoreEntrate;

        return $this;
    }

    /**
     * Get mon_generatore_entrate
     *
     * @return boolean 
     */
    public function getMonGeneratoreEntrate()
    {
        return $this->mon_generatore_entrate;
    }

    /**
     * Set mon_fondo_di_fondi
     *
     * @param boolean $monFondoDiFondi
     * @return Richiesta
     */
    public function setMonFondoDiFondi($monFondoDiFondi)
    {
        $this->mon_fondo_di_fondi = $monFondoDiFondi;

        return $this;
    }

    /**
     * Get mon_fondo_di_fondi
     *
     * @return boolean 
     */
    public function getMonFondoDiFondi()
    {
        return $this->mon_fondo_di_fondi;
    }
	
    /**
     * Add oggetti_richiesta
     *
     * @param \RichiesteBundle\Entity\OggettoRichiesta $oggettiRichiesta
     * @return Richiesta
     */
    public function addOggettiRichiestum(\RichiesteBundle\Entity\OggettoRichiesta $oggettiRichiesta)
    {
        $this->oggetti_richiesta[] = $oggettiRichiesta;

        return $this;
    }

    /**
     * Remove oggetti_richiesta
     *
     * @param \RichiesteBundle\Entity\OggettoRichiesta $oggettiRichiesta
     */
    public function removeOggettiRichiestum(\RichiesteBundle\Entity\OggettoRichiesta $oggettiRichiesta)
    {
        $this->oggetti_richiesta->removeElement($oggettiRichiesta);
    }

    /**
     * Add documenti_richiesta
     *
     * @param \RichiesteBundle\Entity\DocumentoRichiesta $documentiRichiesta
     * @return Richiesta
     */
    public function addDocumentiRichiestum(\RichiesteBundle\Entity\DocumentoRichiesta $documentiRichiesta)
    {
        $this->documenti_richiesta[] = $documentiRichiesta;

        return $this;
    }

    /**
     * Remove documenti_richiesta
     *
     * @param \RichiesteBundle\Entity\DocumentoRichiesta $documentiRichiesta
     */
    public function removeDocumentiRichiestum(\RichiesteBundle\Entity\DocumentoRichiesta $documentiRichiesta)
    {
        $this->documenti_richiesta->removeElement($documentiRichiesta);
    }

    /**
     * Add proponenti
     *
     * @param \RichiesteBundle\Entity\Proponente $proponenti
     * @return Richiesta
     */
    public function addProponenti(\RichiesteBundle\Entity\Proponente $proponenti)
    {
        $this->proponenti[] = $proponenti;

        return $this;
    }

    /**
     * Remove proponenti
     *
     * @param \RichiesteBundle\Entity\Proponente $proponenti
     */
    public function removeProponenti(\RichiesteBundle\Entity\Proponente $proponenti)
    {
        $this->proponenti->removeElement($proponenti);
    }

    /**
     * Add richieste_protocollo
     *
     * @param \ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento $richiesteProtocollo
     * @return Richiesta
     */
    public function addRichiesteProtocollo(\ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento $richiesteProtocollo)
    {
        $this->richieste_protocollo[] = $richiesteProtocollo;

        return $this;
    }

    /**
     * Remove richieste_protocollo
     *
     * @param \ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento $richiesteProtocollo
     */
    public function removeRichiesteProtocollo(\ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento $richiesteProtocollo)
    {
        $this->richieste_protocollo->removeElement($richiesteProtocollo);
    }

    /**
     * Add voci_fase_procedurale
     *
     * @param \RichiesteBundle\Entity\VoceFaseProcedurale $vociFaseProcedurale
     * @return Richiesta
     */
    public function addVociFaseProcedurale(\RichiesteBundle\Entity\VoceFaseProcedurale $vociFaseProcedurale)
    {
        $this->voci_fase_procedurale[] = $vociFaseProcedurale;

        return $this;
    }

    public function setIstruttoria(IstruttoriaRichiesta $istruttoria = null): self
    {
        $this->istruttoria = $istruttoria;
		$this->istruttoria->setRichiesta($this);
        return $this;
    }

    public function getIstruttoria(): ?IstruttoriaRichiesta
    {
        return $this->istruttoria;
    }

    public function setAttuazioneControllo(AttuazioneControlloRichiesta $attuazioneControllo = null): self {
        $this->attuazione_controllo = $attuazioneControllo;
		$this->attuazione_controllo->setRichiesta($this);
        return $this;
    }

    public function getAttuazioneControllo(): ?AttuazioneControlloRichiesta
    {
        return $this->attuazione_controllo;
    }

    public function addAuditCampioni(AuditCampione $auditCampioni): self  {
        $this->audit_campioni[] = $auditCampioni;

        return $this;
    }

    public function removeAuditCampioni(AuditCampione $auditCampioni): void {
        $this->audit_campioni->removeElement($auditCampioni);
    }

    /**
     * Add audit_campioni_operazioni
     *
     * @param \AuditBundle\Entity\AuditCampioneOperazione $auditCampioniOperazioni
     * @return Richiesta
     */
    public function addAuditCampioniOperazioni(\AuditBundle\Entity\AuditCampioneOperazione $auditCampioniOperazioni)
    {
        $this->audit_campioni_operazioni[] = $auditCampioniOperazioni;

        return $this;
    }

    /**
     * Remove audit_campioni_operazioni
     *
     * @param \AuditBundle\Entity\AuditCampioneOperazione $auditCampioniOperazioni
     */
    public function removeAuditCampioniOperazioni(\AuditBundle\Entity\AuditCampioneOperazione $auditCampioniOperazioni)
    {
        $this->audit_campioni_operazioni->removeElement($auditCampioniOperazioni);
    }

    /**
     * Add obiettivi_realizzativi
     *
     * @param \RichiesteBundle\Entity\ObiettivoRealizzativo $obiettiviRealizzativi
     * @return Richiesta
     */
    public function addObiettiviRealizzativi(\RichiesteBundle\Entity\ObiettivoRealizzativo $obiettiviRealizzativi)
    {
        $this->obiettivi_realizzativi[] = $obiettiviRealizzativi;

        return $this;
    }

    /**
     * Remove obiettivi_realizzativi
     *
     * @param \RichiesteBundle\Entity\ObiettivoRealizzativo $obiettiviRealizzativi
     */
    public function removeObiettiviRealizzativi(\RichiesteBundle\Entity\ObiettivoRealizzativo $obiettiviRealizzativi)
    {
        $this->obiettivi_realizzativi->removeElement($obiettiviRealizzativi);
    }

    /**
     * Add atti
     *
     * @param \SfingeBundle\Entity\Atto $atti
     * @return Richiesta
     */
    public function addAtti(\SfingeBundle\Entity\Atto $atti)
    {
        $this->atti[] = $atti;

        return $this;
    }

    /**
     * Remove atti
     *
     * @param \SfingeBundle\Entity\Atto $atti
     */
    public function removeAtti(\SfingeBundle\Entity\Atto $atti)
    {
        $this->atti->removeElement($atti);
    }

    /**
     * Set mon_tipo_aiuto
     *
     * @param \MonitoraggioBundle\Entity\TC6TipoAiuto $monTipoAiuto
     * @return Richiesta
     */
    public function setMonTipoAiuto(\MonitoraggioBundle\Entity\TC6TipoAiuto $monTipoAiuto = null)
    {
        $this->mon_tipo_aiuto = $monTipoAiuto;

        return $this;
    }

    /**
     * Get mon_tipo_aiuto
     *
     * @return \MonitoraggioBundle\Entity\TC6TipoAiuto 
     */
    public function getMonTipoAiuto()
    {
        return $this->mon_tipo_aiuto;
    }

    public function setMonTipoOperazione(TC5TipoOperazione $monTipoOperazione = null): self {
        $this->mon_tipo_operazione = $monTipoOperazione;

        return $this;
    }

    public function getMonTipoOperazione(): ?TC5TipoOperazione {
        return $this->mon_tipo_operazione;
    }

    public function addMonProgrammi(RichiestaProgramma $monProgrammi): self {
        $this->mon_programmi[] = $monProgrammi;

        return $this;
    }

    public function removeMonProgrammi(RichiestaProgramma $monProgrammi): void {
        $this->mon_programmi->removeElement($monProgrammi);
    }

    /**
     * @return Collection|RichiestaProgramma[]
     */
    public function getMonProgrammi(): Collection {
        return $this->mon_programmi;
    }

    public function addMonStrumentiAttuativi(StrumentoAttuativo $monStrumentiAttuativi): self {
        $this->mon_strumenti_attuativi[] = $monStrumentiAttuativi;

        return $this;
    }

    public function removeMonStrumentiAttuativi(StrumentoAttuativo $monStrumentiAttuativi): void {
        $this->mon_strumenti_attuativi->removeElement($monStrumentiAttuativi);
    }

    /**
     * @return StrumentoAttuativo[]|Collection 
     */
    public function getMonStrumentiAttuativi(): Collection {
        return $this->mon_strumenti_attuativi;
    }

    public function addMonProcedureAggiudicazione(\AttuazioneControlloBundle\Entity\ProceduraAggiudicazione $monProcedureAggiudicazione): self {
        $this->mon_procedure_aggiudicazione[] = $monProcedureAggiudicazione;

        return $this;
    }

    public function removeMonProcedureAggiudicazione(\AttuazioneControlloBundle\Entity\ProceduraAggiudicazione $monProcedureAggiudicazione): void {
        $this->mon_procedure_aggiudicazione->removeElement($monProcedureAggiudicazione);
    }

    /**
     * @return Collection|\AttuazioneControlloBundle\Entity\ProceduraAggiudicazione[]
     */
    public function getMonProcedureAggiudicazione(): Collection
    {
        return $this->mon_procedure_aggiudicazione;
    }

    public function setMonTipoProceduraAttOrig(\MonitoraggioBundle\Entity\TC48TipoProceduraAttivazioneOriginaria $monTipoProceduraAttOrig = null): self {
        $this->mon_tipo_procedura_att_orig = $monTipoProceduraAttOrig;

        return $this;
    }

    /**
     * Get mon_tipo_procedura_att_orig
     *
     * @return \MonitoraggioBundle\Entity\TC48TipoProceduraAttivazioneOriginaria 
     */
    public function getMonTipoProceduraAttOrig()
    {
        return $this->mon_tipo_procedura_att_orig;
    }

    public function addMonIterProgetti(IterProgetto $monIterProgetti): self
    {
        $this->mon_iter_progetti[] = $monIterProgetti;

        return $this;
    }

    public function addMonIterProgettus(IterProgetto $monIterProgetti): self {
        return $this->addMonIterProgetti($monIterProgetti);
    }

    public function removeMonIterProgetti(IterProgetto $monIterProgetti): void {
        $this->mon_iter_progetti->removeElement($monIterProgetti);
    }

    public function removeMonIterProgettus(IterProgetto $monIterProgetti): void {
        $this->removeMonIterProgetti($monIterProgetti);
    }

    /**
     * @return Collection|\AttuazioneControlloBundle\Entity\IterProgetto[] 
     */
    public function getMonIterProgetti(): Collection
    {
        return $this->mon_iter_progetti;
    }

    public function addMonStatoProgetti(RichiestaStatoAttuazioneProgetto $monStatoProgetti): self {
        $this->mon_stato_progetti[] = $monStatoProgetti;

        return $this;
    }

    public function addMonStatoProgettus(RichiestaStatoAttuazioneProgetto $addMonStatoProgetti): self {
        return $this->addMonStatoProgetti($addMonStatoProgetti);
    }

    
    public function removeMonStatoProgetti(RichiestaStatoAttuazioneProgetto $monStatoProgetti): void {
        $this->mon_stato_progetti->removeElement($monStatoProgetti);
    }

    public function removeMonStatoProgettus(RichiestaStatoAttuazioneProgetto $monStatoProgetti): void {
        $this->removeMonStatoProgetti($monStatoProgetti);
    }

    /**
     * @return RichiestaStatoAttuazioneProgetto[]|Collection 
     */
    public function getMonStatoProgetti(): Collection {
        return $this->mon_stato_progetti;
    }

    public function setMonProgettoComplesso(?TC7ProgettoComplesso $monProgettoComplesso = null): self {
        $this->mon_progetto_complesso = $monProgettoComplesso;

        return $this;
    }

    public function getMonProgettoComplesso(): ?TC7ProgettoComplesso {
        return $this->mon_progetto_complesso;
    }


    public function setMonGrandeProgetto(?TC8GrandeProgetto $monGrandeProgetto = null): self {
        $this->mon_grande_progetto = $monGrandeProgetto;

        return $this;
    }

    public function getMonGrandeProgetto(): ?TC8GrandeProgetto {
        return $this->mon_grande_progetto;
    }

    public function setMonLivIstituzioneStrFin(?TC9TipoLivelloIstituzione $monLivIstituzioneStrFin = null): self {
        $this->mon_liv_istituzione_str_fin = $monLivIstituzioneStrFin;

        return $this;
    }

    public function getMonLivIstituzioneStrFin(): ?TC9TipoLivelloIstituzione {
        return $this->mon_liv_istituzione_str_fin;
    }

    public function addMonIndicatoreOutput(IndicatoreOutput $monIndicatoreOutput): self
    {
        $this->mon_indicatore_output[] = $monIndicatoreOutput;

        return $this;
    }

    public function removeMonIndicatoreOutput(IndicatoreOutput $monIndicatoreOutput): void
    {
        $this->mon_indicatore_output->removeElement($monIndicatoreOutput);
    }

    /**
     * @return \RichiesteBundle\Entity\IndicatoreOutput[]|Collection
     */
    public function getMonIndicatoreOutput(?bool $responsabilitaUtente = null): Collection
    {
        if(\is_null($responsabilitaUtente)){
            return $this->mon_indicatore_output;
        }
        return $this->mon_indicatore_output->filter(function(IndicatoreOutput $indicatore) use($responsabilitaUtente){
            return $indicatore->getIndicatore()->getResponsabilitaUtente() == $responsabilitaUtente;
        });
    }

    public function setMonIndicatoreOutput(Collection $indicatori): self
    {
        $this->mon_indicatore_output = $indicatori;

        return $this;
    }

    public function addMonIndicatoreRisultato(IndicatoreRisultato $monIndicatoreRisultato): self
    {
        $this->mon_indicatore_risultato[] = $monIndicatoreRisultato;

        return $this;
    }

    /**
     * @param \RichiesteBundle\Entity\IndicatoreRisultato $monIndicatoreRisultato
     */
    public function removeMonIndicatoreRisultato(IndicatoreRisultato $monIndicatoreRisultato)
    {
        $this->mon_indicatore_risultato->removeElement($monIndicatoreRisultato);
    }

    /**
     * @return Collection|\RichiesteBundle\Entity\IndicatoreRisultato[]
     */
    public function getMonIndicatoreRisultato(): Collection
    {
        return $this->mon_indicatore_risultato;
    }

    public function addMonSoggettiCorrelati(SoggettiCollegati $monSoggettiCorrelati): self
    {
        $this->mon_soggetti_correlati[] = $monSoggettiCorrelati;

        return $this;
    }

    public function removeMonSoggettiCorrelati(SoggettiCollegati $monSoggettiCorrelati): void
    {
        $this->mon_soggetti_correlati->removeElement($monSoggettiCorrelati);
    }

    /**
     * @return SoggettiCollegati[]|Collection 
     */
    public function getMonSoggettiCorrelati(): Collection
    {
        return $this->mon_soggetti_correlati;
    }

    public function addMonFinanziamenti(Finanziamento $monFinanziamenti): self
    {
        $this->mon_finanziamenti[] = $monFinanziamenti;

        return $this;
    }

    public function removeMonFinanziamenti(Finanziamento $monFinanziamenti): void
    {
        $this->mon_finanziamenti->removeElement($monFinanziamenti);
    }

    /**
     * @param string|null $fonte Se diverso da NULL effettua un filtro secondo la fonte finanziaria
     * @return Collection|\AttuazioneControlloBundle\Entity\Finanziamento[]
     */
    public function getMonFinanziamenti(string $fonte = null): Collection  {
        if(\is_null($fonte)){
            return $this->mon_finanziamenti;
        }
        
        return $this->mon_finanziamenti->filter(function(Finanziamento $f) use($fonte) {
            return $f->getTc33FonteFinanziaria()->getCodFondo() == $fonte;
        });
    }

    public function addMonEconomie(Economia $monEconomie): self
    {
        $this->mon_economie[] = $monEconomie;

        return $this;
    }

    public function removeMonEconomie(Economia $monEconomie): void
    {
        $this->mon_economie->removeElement($monEconomie);
    }

    /**
     * @return Collection|Economia[]
     */
    public function getMonEconomie(): Collection
    {
        return $this->mon_economie;
    }

    public function addMonImpegni(RichiestaImpegni $monImpegni): self
    {
        $this->mon_impegni[] = $monImpegni;

        return $this;
    }

    public function removeMonImpegni(RichiestaImpegni $monImpegni): void
    {
        $this->mon_impegni->removeElement($monImpegni);
    }

    /**
     * @return Collection|RichiestaImpegni[]
     */
    public function getMonImpegni(): Collection
    {
        return $this->mon_impegni;
    }

    public function getTotaleImportoImpegni():float{
        return \array_reduce($this->mon_impegni->toArray(), function(float $carry, RichiestaImpegni $impegno): float {
            return $carry +
                $impegno->getImportoImpegno() * (
                    \in_array($impegno->getTipologiaImpegno(), [RichiestaImpegni::DISIMPEGNO]) ?
                        -1 : 1
                    );
        }, 0.0);
    }

    public function addMonRichiestePagamento(RichiestaPagamento $monRichiestePagamento): self
    {
        $this->mon_richieste_pagamento[] = $monRichiestePagamento;

        return $this;
    }

    public function removeMonRichiestePagamento(RichiestaPagamento $monRichiestePagamento): void
    {
        $this->mon_richieste_pagamento->removeElement($monRichiestePagamento);
    }

    /**
     * @return Collection|RichiestaPagamento[]
     */
    public function getMonRichiestePagamento(): Collection
    {
        return $this->mon_richieste_pagamento;
    }

    public function addMonSpeseCertificate(RichiestaSpesaCertificata $monSpeseCertificate): self
    {
        $this->mon_spese_certificate[] = $monSpeseCertificate;

        return $this;
    }

    /**
     * @param \AttuazioneControlloBundle\Entity\RichiestaSpesaCertificata $monSpeseCertificate
     */
    public function removeMonSpeseCertificate(RichiestaSpesaCertificata $monSpeseCertificate)
    {
        $this->mon_spese_certificate->removeElement($monSpeseCertificate);
    }

    /**
     * @return Collection|RichiestaSpesaCertificata[]
     */
    public function getMonSpeseCertificate(): Collection
    {
        return $this->mon_spese_certificate;
    }

    /**
     * Add mon_piano_costi
     *
     * @param \MonitoraggioBundle\Entity\RichiestaPianoCosti $monPianoCosti
     * @return Richiesta
     */
    public function addMonPianoCosti(\MonitoraggioBundle\Entity\RichiestaPianoCosti $monPianoCosti): self
    {
        $this->mon_piano_costi[] = $monPianoCosti;

        return $this;
    }

    /**
     * Remove mon_piano_costi
     *
     * @param \MonitoraggioBundle\Entity\RichiestaPianoCosti $monPianoCosti
     */
    public function removeMonPianoCosti(\MonitoraggioBundle\Entity\RichiestaPianoCosti $monPianoCosti): void
    {
        $this->mon_piano_costi->removeElement($monPianoCosti);
    }

    /**
     * Get mon_piano_costi
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMonPianoCosti(): Collection
    {
        return $this->mon_piano_costi;
    }

    /**
     * Add mon_voce_spesa
     *
     * @param \MonitoraggioBundle\Entity\VoceSpesa $monVoceSpesa
     * @return Richiesta
     */
    public function addMonVoceSpesa(VoceSpesa $monVoceSpesa): self
    {
        $this->mon_voce_spesa[] = $monVoceSpesa;

        return $this;
    }

    public function removeMonVoceSpesa(VoceSpesa $monVoceSpesa): void
    {
        $this->mon_voce_spesa->removeElement($monVoceSpesa);
    }

    /**
     * @return Collection|VoceSpesa[]
     */
    public function getMonVoceSpesa(): Collection
    {
        return $this->mon_voce_spesa;
    }

    public function addMonLocalizzazioneGeografica(LocalizzazioneGeografica $monLocalizzazioneGeografica): self
    {
        $this->mon_localizzazione_geografica[] = $monLocalizzazioneGeografica;

        return $this;
    }

    public function removeMonLocalizzazioneGeografica(LocalizzazioneGeografica $monLocalizzazioneGeografica): void
    {
        $this->mon_localizzazione_geografica->removeElement($monLocalizzazioneGeografica);
    }

    /**
     * @return \MonitoraggioBundle\Entity\LocalizzazioneGeografica[]|Collection 
     */
    public function getMonLocalizzazioneGeografica(): Collection
    {
        return $this->mon_localizzazione_geografica;
    }

    public function setMonTipoLocalizzazione(TC10TipoLocalizzazione $monTipoLocalizzazione = null): self
    {
        $this->mon_tipo_localizzazione = $monTipoLocalizzazione;

        return $this;
    }

    public function getMonTipoLocalizzazione():?TC10TipoLocalizzazione
    {
        return $this->mon_tipo_localizzazione;
    }

    public function setMonGruppoVulnerabile(?TC13GruppoVulnerabileProgetto $monGruppoVulnerabile = null): self
    {
        $this->mon_gruppo_vulnerabile = $monGruppoVulnerabile;

        return $this;
    }

    public function getMonGruppoVulnerabile(): ?TC13GruppoVulnerabileProgetto
    {
        return $this->mon_gruppo_vulnerabile;
    }

    public function setMonStrumentiAttuativi(Collection $monStrumentiAttuativi): self
    {
        $this->mon_strumenti_attuativi = $monStrumentiAttuativi;

        return $this;
    }

	public function getControlli() {
		return $this->controlli;
	}

	public function setControlli($controlli) {
		$this->controlli = $controlli;
	}
	
	public function hasControlliProgetto(){
            $count = 0;
            foreach ($this->controlli as $controllo) {
                if($controllo->getTipologia() == 'STANDARD') {
                    $count++;
                }
            }
            return $count > 0;
	}
	
	public function importoRendicontatoAmmesso() {
		$importoRendicontatoAmmesso = 0.00;
		foreach ($this->attuazione_controllo->getPagamenti() as $pagamento) {
			$importoRendicontatoAmmesso += $pagamento->calcolaImportoTotaleRichiestoAmmessoDaVoci();
		}
		return $importoRendicontatoAmmesso;
	}	
	
	public function getIncarichiRichiesta(): Collection {
		return $this->incarichi_richiesta;
	}

	public function setIncarichiRichiesta(Collection $incarichi_richiesta) {
		$this->incarichi_richiesta = $incarichi_richiesta;
	}
	
	public function getAccettazioneAutodichiarazioni() {
		return $this->accettazione_autodichiarazioni;
	}

	public function setAccettazioneAutodichiarazioni($accettazione_autodichiarazioni) {
		$this->accettazione_autodichiarazioni = $accettazione_autodichiarazioni;
	}

    /**
     * @return Richiesta
     * @throws SfingeException
     */
    public function setSoggetto(Soggetto $soggetto) {
        $mandatario = $this->getMandatario();   /** @var Proponente $mandatario */
        if(\is_null($mandatario)){
            throw new SfingeException('Mandatario non definito');
        }
        $mandatario->setSoggetto($soggetto);

        return $this;
    }
	
	public function getSedeMontana() {
		return $this->sede_montana;
	}

	public function setSedeMontana($sede_montana) {
		$this->sede_montana = $sede_montana;
	}
	
	public function getRisorseProgetto() {
		return $this->risorse_progetto;
	}

	public function setRisorseProgetto($risorse_progetto) {
		$this->risorse_progetto = $risorse_progetto;
	}

    public function addRisorseProgetto(\RichiesteBundle\Entity\RisorsaProgetto $risorse_progetto)
    {
        $this->risorse_progetto[] = $risorse_progetto;

        return $this;
    }

    public function removeRisorseProgetto(\RichiesteBundle\Entity\RisorsaProgetto $risorse_progetto)
    {
        $this->risorse_progetto->removeElement($risorse_progetto);
    }

	public function hasCampionamentoLoco() {
		return count($this->controlli) > 0;
	}
	
	public function hasCampionamentoLocoConcluso() {
		if($this->hasCampionamentoLoco() == true) {
			foreach ($this->controlli  as $controllo) {
				if(!is_null($controllo->getEsito())) {
					return true;
				}
			}
		}
		return false;
	} 
	
	public function getNotaFaseProcedurale() {
		return $this->nota_fase_procedurale;
	}

	public function setNotaFaseProcedurale($nota_fase_procedurale) {
		$this->nota_fase_procedurale = $nota_fase_procedurale;
	}

	public function getAiutoStatoProgetto() {
		return $this->aiuto_stato_progetto;
	}

	public function setAiutoStatoProgetto($aiuto_stato_progetto) {
		$this->aiuto_stato_progetto = $aiuto_stato_progetto;
	}
	
	public function getAiutoDiStato() {
		return !is_null($this->aiuto_stato_progetto) ? $this->aiuto_stato_progetto : $this->procedura->getAiutoStato();
	}
	
	/*
	 * Dati di appoggio per ass. tec. e acquisizioni
	 * INIZIO DATI APPOGGIO
	 */
	
	/**
     * @Assert\NotNUll(groups={"ASS_TEC_ACQ"})
	 */
	protected $data_inizio_progetto;
	/**
     * @Assert\NotNUll(groups={"ASS_TEC_ACQ"})
	 */
	protected $data_fine_progetto;
	
	public function getDataInizioProgetto() {
		return $this->data_inizio_progetto;
	}

	public function getDataFineProgetto() {
		return $this->data_fine_progetto;
	}
	
	public function getRegistro() {
		return $this->registro;
	}

	public function setRegistro($registro) {
		$this->registro = $registro;
	}

	public function getCodiceCup() {
		if (!is_null($this->istruttoria) && !is_null($this->istruttoria->getCodiceCup())) {
			return $this->istruttoria->getCodiceCup();
		} elseif (!is_null($this->attuazione_controllo) && !is_null($this->attuazione_controllo->getCup())) {
			return $this->attuazione_controllo->getCup();
		}else {
			return '-';
		}
	}
	public function setDataInizioProgetto($data_inizio_progetto) {
		$this->data_inizio_progetto = $data_inizio_progetto;
	}

	public function setDataFineProgetto($data_fine_progetto) {
		$this->data_fine_progetto = $data_fine_progetto;
	}
	/*
	 * Dati di appoggio per ass. tec. e acquisizioni
	 * FINE DATI APPOGGIO
	*/

    public function isIndicatorePresente(TC44_45IndicatoriOutput $def): bool {
        foreach ($this->getMonIndicatoreOutput() as $indicatore) {
            if($indicatore->getIndicatore() == $def){
                return true;
            }
        }
        return false;
    }

    public function getTotalePianoCosto(): float
    {
        return \array_reduce($this->proponenti->toArray(), function(float $carry, Proponente $p){
            return $carry + $p->getTotalePianoCosti();
        }, 0.0);
    }

    public function addComunicazioniProgetto(ComunicazioneProgetto $comunicazioniProgetto): self
    {
        $this->comunicazioni_progetto[] = $comunicazioniProgetto;

        return $this;
    }

    public function removeComunicazioniProgetto(ComunicazioneProgetto $comunicazioniProgetto): void
    {
        $this->comunicazioni_progetto->removeElement($comunicazioniProgetto);
    }


    public function addControlli(ControlloProgetto $controlli): self
    {
        $this->controlli[] = $controlli;

        return $this;
    }


    public function removeControlli(ControlloProgetto $controlli): void
    {
        $this->controlli->removeElement($controlli);
    }

    public function addIncarichiRichiestum(\SoggettoBundle\Entity\IncaricoPersonaRichiesta $incarichiRichiestum): self
    {
        $this->incarichi_richiesta[] = $incarichiRichiestum;

        return $this;
    }

    public function removeIncarichiRichiestum(\SoggettoBundle\Entity\IncaricoPersonaRichiesta $incarichiRichiestum): void
    {
        $this->incarichi_richiesta->removeElement($incarichiRichiestum);
    }
	
	public function ultimaRevoca() {
		return $this->attuazione_controllo->getRevoca()->last();
	}
	
	public function getUltimoAttoRevoca() {
		if($this->attuazione_controllo->getRevoca()->count() > 0) {
			if(!is_null($this->ultimaRevoca()->getAttoRevoca())) {
				return $this->ultimaRevoca()->getAttoRevoca();
			}
		}
		return null;
	}

    public function setFlagPor(?bool $flagPor): self {
        $this->flag_por = $flagPor;

        return $this;
    }

    public function getFlagPor(): ?bool
    {
        return $this->flag_por;
    }

    public function setFlagInviatoMonit(?bool $flagInviatoMonit): self {
        $this->flag_inviato_monit = $flagInviatoMonit;

        return $this;
    }

    public function getFlagInviatoMonit(): ?bool
    {
        return $this->flag_inviato_monit;
    }

    public function addRegistro(\CertificazioniBundle\Entity\RegistroDebitori $registro): self
    {
        $this->registro[] = $registro;

        return $this;
    }

    public function removeRegistro(\CertificazioniBundle\Entity\RegistroDebitori $registro): void
    {
        $this->registro->removeElement($registro);
    }

    public function getTotaleFinanziamento(): float {
        return \array_reduce($this->getMonFinanziamenti()->toArray(), function (float $carry, Finanziamento $finanziamento) {
            return $carry + $finanziamento->getImporto();
        }, 0.0);
    }
    
    /**
     * @return Collection|AssegnamentoIstruttoriaRichiesta[]
     */
    public function getAssegnamentiIstruttoria(): Collection
    {
        return $this->assegnamenti_istruttoria;
    }

    /**
     * @param Collection $assegnamenti_istruttoria
     * @return Richiesta
     */
    public function setAssegnamentiIstruttoria(Collection $assegnamenti_istruttoria): self
    {
        $this->assegnamenti_istruttoria = $assegnamenti_istruttoria;
        
        return $this;
    }

    /**
     * @return AssegnamentoIstruttoriaRichiesta|null
     */
    public function getAssegnamentoIstruttoriaAttivo(): ?AssegnamentoIstruttoriaRichiesta
    {
        $istruttore = $this->assegnamenti_istruttoria->filter(
            function(AssegnamentoIstruttoriaRichiesta $assegnamento): bool {
                return $assegnamento->isAttivo();
            })
            ->last();

        return $istruttore ?: null;
    }
    
    public function getInterventoSede(): Collection {
        return $this->intervento_sede;
    }

    public function setInterventoSede(Collection $intervento_sede) {
        $this->intervento_sede = $intervento_sede;
    }


    public function setMonPrgPubblico(bool $monPrgPubblico): self {
        $this->mon_prg_pubblico = $monPrgPubblico;

        return $this;
    }

    public function getMonPrgPubblico(): bool {
        return $this->mon_prg_pubblico;
    }

    public function addComunicazioniAttuazione(ComunicazioneAttuazione $comunicazioniAttuazione): self {
        $this->comunicazioni_attuazione[] = $comunicazioniAttuazione;

        return $this;
    }

    public function removeComunicazioniAttuazione(ComunicazioneAttuazione $comunicazioniAttuazione): void {
        $this->comunicazioni_attuazione->removeElement($comunicazioniAttuazione);
    }

    public function addAssegnamentiIstruttorium(\IstruttorieBundle\Entity\AssegnamentoIstruttoriaRichiesta $assegnamentiIstruttorium): self {
        $this->assegnamenti_istruttoria[] = $assegnamentiIstruttorium;

        return $this;
    }

    public function removeAssegnamentiIstruttorium(\IstruttorieBundle\Entity\AssegnamentoIstruttoriaRichiesta $assegnamentiIstruttorium): void {
        $this->assegnamenti_istruttoria->removeElement($assegnamentiIstruttorium);
    }

    public function addInterventoSede(InterventoSede $interventoSede): self {
        $this->intervento_sede[] = $interventoSede;

        return $this;
    }

    public function removeInterventoSede(InterventoSede $interventoSede): void {
        $this->intervento_sede->removeElement($interventoSede);
    }

    /**
     * @Assert\IsTrue(groups={"impegni_beneficiario"})
     */
    public function isImpegniValid(): bool {
        if(\is_null($this->attuazione_controllo)){
            return true;
            }

        $importoImpegni = \array_reduce($this->mon_impegni->toArray(), function (float $carry, RichiestaImpegni $impegno) {
            return \bcadd($carry, $impegno->getImportoImpegno());
        }, 0.0);

        $contributoErogato = $this->attuazione_controllo->getContributoErogato();
        if (\bccomp($importoImpegni, $contributoErogato) < 0) {
            return false;
        }

        return true;
    }

    /**
     * @return Collection
     */
    public function getProgrammi(): Collection {
        return $this->programmi;
    }
    
    
    public function getDescrizioneStatoProgetto() {
        $stato = 'in attuazione';
        if(($this->hasPagamentoSaldo() == true || $this->hasPagamentoUnicaSoluzione() == true)) {
           $stato = 'concluso';  
        }
        if($this->ultimaRevoca() != false) {
            $stato = 'revoca parziale'; 
        }
        if(!is_null($this->getUltimoAttoRevoca()) && $this->getUltimoAttoRevoca()->getTipo()->getCodice() == 'PAR') {
            $stato = 'revoca parziale'; 
        }
        if(!is_null($this->getUltimoAttoRevoca()) && $this->getUltimoAttoRevoca()->getTipo()->getCodice() == 'RIN') {
            $stato = 'rinuncia'; 
        }
        if(!is_null($this->getUltimoAttoRevoca()) && $this->getUltimoAttoRevoca()->getTipo()->getCodice() == 'TOT') {
            $stato = 'revoca totale'; 
        }
        return $stato;
    }
    
    public function hasPagamentoSaldo() {
        foreach ($this->attuazione_controllo->getPagamenti() as $pagamento) {
            if($pagamento->getModalitaPagamento()->getCodice() == 'SALDO_FINALE' && !is_null($pagamento->getEsitoIstruttoria()) && !is_null($pagamento->getMandatoPagamento())) {
                return true;
            }
        }
        return false;
    }
    
    public function hasPagamentoUnicaSoluzione() {
        foreach ($this->attuazione_controllo->getPagamenti() as $pagamento) {
            if($pagamento->getModalitaPagamento()->getCodice() == 'UNICA_SOLUZIONE' && !is_null($pagamento->getEsitoIstruttoria()) && !is_null($pagamento->getMandatoPagamento())) {
                return true;
            }
        }
        return false;
    }

    public function addFornitori(Fornitore $fornitori): self {
        $this->fornitori[] = $fornitori;

        return $this;
    }

    public function removeFornitori(Fornitore $fornitori): void {
        $this->fornitori->removeElement($fornitori);
    }

    /**
     * @return Collection|Fornitore[]
     */
    public function getFornitori(): Collection {
        return $this->fornitori;
    }

    public function getUtenteInvio() {
        return $this->utente_invio;
    }

    public function setUtenteInvio($utente_invio) {
        $this->utente_invio = $utente_invio;
    }
    
    public function isFinestraPresentazioneAbilitata() {
        $procedureDaControllare = [95, 167];
        if (in_array($this->getProcedura()->getId(), $procedureDaControllare)) {
            if ($this->getFinestraTemporale() < $this->getProcedura()->getAttualeFinestraTemporalePresentazione()) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * @return DocumentoIstruttoria[]|Collection
     */
    public function getDocumentiIstruttoria(): Collection
    {
        return $this->documenti_istruttoria;
    }

    /**
     * @return Collection|SedeOperativaRichiesta[]
     */
    public function getSediOperative()
    {
        return $this->sedi_operative;
    }

    /**
     * @param Collection|SedeOperativaRichiesta[] $sedi_operative
     */
    public function setSediOperative($sedi_operative): void
    {
        $this->sedi_operative = $sedi_operative;
    }
    
    public function getTotalePagato() {
        $pagato = 0.00;
        foreach ($this->getAttuazioneControllo()->getPagamenti() as $pagamento) {
            if(!is_null($pagamento->getMandatoPagamento())) {
                $pagato += $pagamento->getMandatoPagamento()->getImportoPagato();
            }
        }
        return $pagato;
    }

    /**
     * @return Persona|null
     */
    public function getPresentatoreRichiesta()
    {
        $presentatore = null;
        if ($this->getProcedura()->isRichiestaFirmaDigitale()) {
            $presentatore = $this->getFirmatario();
        } else {
            // Recupero il presentatore in questo modo perché
            // il campo "utente_invio" della tabella richieste contiene
            // il codice fiscale della persona e non l'id
            $soggetto = $this->getMandatario()->getSoggetto();
            foreach ($soggetto->getIncarichiPersone() as $incaricoPersona) {
                if ($incaricoPersona->getIncaricato()->getCodiceFiscale() == $this->getUtenteInvio()) {
                    $presentatore = $incaricoPersona->getIncaricato();
                }
            }
        }

        return $presentatore;
    }

    /**
     * @return array
     */
    public function getIncarichiPresentatoreRichiesta()
    {
        $incarichi = [];
        $tipologiaIncarichiPresentatore = ['LR', 'DELEGATO'];
        $presentatore = $this->getPresentatoreRichiesta();
        $soggetto = $this->getMandatario()->getSoggetto();
        foreach ($soggetto->getIncarichiPersone() as $incaricoPersona) {
            if ($incaricoPersona->getIncaricato() == $presentatore
                && in_array($incaricoPersona->getTipoIncarico()->getCodice(), $tipologiaIncarichiPresentatore)) {
                $presentatore = $incaricoPersona->getIncaricato();
                $incarichi[] = $incaricoPersona;
            }
        }

        return $incarichi;
    }

    /**
     * @param bool $returnCodice
     * @return string|null
     */
    public function ruoloFirmatario(bool $returnCodice = false): ?string
    {
        $codiceFiscaleFirmatario = $this->getFirmatario()->getCodiceFiscale();
        foreach ($this->getProponenti() as $proponente) {
            $soggetto = $proponente->getSoggetto();
            foreach ($soggetto->getIncarichiPersone() as $incaricoPersona) {
                if ($codiceFiscaleFirmatario == $incaricoPersona->getIncaricato()->getCodiceFiscale() && $incaricoPersona->isAttivo()
                    && ($incaricoPersona->getTipoIncarico()->getCodice() == TipoIncarico::LR || $incaricoPersona->getTipoIncarico()->getCodice() == TipoIncarico::DELEGATO)) {
                    if ($returnCodice) {
                        return $incaricoPersona->getTipoIncarico()->getCodice();
                    } else {
                        return $incaricoPersona->getTipoIncarico()->getDescrizione();
                    }
                }
            }
        }
        return '';
    }
    
    public function getDichiarazioneDnsh(): ?DichiarazioneDsnh {
        return $this->dichiarazione_dnsh;
    }

    public function setDichiarazioneDnsh(?DichiarazioneDsnh $dichiarazione_dnsh): void {
        $this->dichiarazione_dnsh = $dichiarazione_dnsh;
    }

    /**
     * @return Collection|PosizioneImpegno[]
     */
    public function getPosizioniImpegni()
    {
        return $this->posizioni_impegni;
    }

    /**
     * @param Collection|PosizioneImpegno[] $posizioni_impegni
     */
    public function setPosizioniImpegni($posizioni_impegni): void
    {
        $this->posizioni_impegni = $posizioni_impegni;
    }
}
