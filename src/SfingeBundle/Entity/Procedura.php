<?php

namespace SfingeBundle\Entity;

use AttuazioneControlloBundle\Entity\RendicontazioneProceduraConfig;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use IstruttorieBundle\Entity\PropostaImpegno;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\Common\Collections\Collection;
use AttuazioneControlloBundle\Entity\Controlli\ControlloProcedura;
use MonitoraggioBundle\Entity\TC44_45IndicatoriOutput;
use MonitoraggioBundle\Entity\IndicatoriOutputAzioni;
use AttuazioneControlloBundle\Entity\TipologiaGiustificativo;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\SezionePianoCosto;
use DocumentoBundle\Entity\TipologiaDocumento;
use AttuazioneControlloBundle\Entity\TipologiaQuietanza;
use MonitoraggioBundle\Entity\TC2TipoProceduraAttivazione;
use AttuazioneControlloBundle\Entity\Controlli\ChecklistControllo;
use AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura;
use AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento;
use ProtocollazioneBundle\Entity\RichiestaProtocollo;
use RichiesteBundle\Entity\PianoCosto;
use MonitoraggioBundle\Entity\TC42_43IndicatoriRisultato;
use MonitoraggioBundle\Entity\IndicatoriRisultatoObiettivoSpecifico;

/**
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\ProceduraRepository")
 * @ORM\Table(name="procedure_operative",
 *  indexes={
 *      @ORM\Index(name="idx_responsabile_id", columns={"responsabile_id"}),
 *      @ORM\Index(name="idx_rup_id", columns={"rup_id"}),
 *      @ORM\Index(name="idx_stato_procedura_id", columns={"stato_procedura_id"}),
 *      @ORM\Index(name="idx_stato_procedura_id", columns={"stato_procedura_id"}),
 *      @ORM\Index(name="idx_atto_id", columns={"atto_id"}),
 *      @ORM\Index(name="idx_asse_id", columns={"asse_id"}),
 *      @ORM\Index(name="idx_amministrazione_emittente_id", columns={"amministrazione_emittente_id"}),
 *      @ORM\Index(name="idx_tipo_procedura_monitoraggio_id", columns={"tipo_procedura_monitoraggio_id"}),
 *      @ORM\Index(name="idx_tipo_iter_id", columns={"tipo_iter_id"}),
 *      @ORM\Index(name="idx_tipo_finanziamento_id", columns={"tipo_finanziamento_id"}),
 *      @ORM\Index(name="idx_fase_id", columns={"fase_id"}),
 *  })
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({"BANDO"="SfingeBundle\Entity\Bando", 
 * 		"MANIFESTAZIONE_INTERESSE"="SfingeBundle\Entity\ManifestazioneInteresse", 
 * 		"ASSISTENZA_TECNICA"="SfingeBundle\Entity\AssistenzaTecnica",
 * 		"INGEGNERIA_FINANZIARIA"="SfingeBundle\Entity\IngegneriaFinanziaria",
 *  	"ACQUISIZIONI"="SfingeBundle\Entity\Acquisizioni",
 * 		"PROCEDURA_PA" = "ProceduraPA"
 * })
 *
 * @Assert\Callback(callback="checkAzioni")
 * @Assert\Callback(callback="checkObiettiviSpecifici")
 */
abstract class Procedura extends EntityLoggabileCancellabile {

    const MON_TIPO_PRG_MISTO = 'MISTO';
    const MON_TIPO_PRG_PUBBLICO = 'PUBBLICO';
    const MON_TIPO_PRG_PRIVATO = 'PRIVATO';

    const MARCA_DA_BOLLO_FISICA = 'FISICA';
    const MARCA_DA_BOLLO_DIGITALE = 'DIGITALE';
    const MARCA_DA_BOLLO_FISICA_E_DIGITALE = 'FISICA_E_DIGITALE';

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente", inversedBy="procedure")
     * @ORM\JoinColumn(name="responsabile_id", referencedColumnName="id", nullable=true)
     *
     * @Assert\NotBlank()
     */
    protected $responsabile;

    /**
     * @ORM\ManyToOne(targetEntity="StatoProcedura")
     * @ORM\JoinColumn(name="stato_procedura_id", referencedColumnName="id", nullable=true)
     *
     * @Assert\NotBlank()
     */
    protected $stato_procedura;

    /**
     * @ORM\ManyToOne(targetEntity="Atto")
     * @ORM\JoinColumn(name="atto_id", referencedColumnName="id", nullable=true)
     *
     * @Assert\NotBlank()
     */
    protected $atto;

    /**
     * @ORM\Column(type="string", length=1000,  name="titolo", nullable=true)
     *
     * @Assert\NotBlank()
     */
    protected $titolo;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, name="risorse_disponibili", nullable=true)
     *
     * @Assert\NotBlank()
     */
    protected $risorse_disponibili;

    /**
     * @ORM\ManyToOne(targetEntity="Asse", inversedBy="procedure")
     * @ORM\JoinColumn(name="asse_id", referencedColumnName="id", nullable=true)
     *
     * @Assert\NotBlank()
     */
    protected $asse;

    /** 	 
     * @ORM\ManyToMany(targetEntity="ObiettivoSpecifico", inversedBy="procedure", cascade={"all"})
     * @ORM\JoinTable(name="procedure_operative_obiettivi_specifici")
     *
     * @Assert\NotBlank()
     */
    protected $obiettivi_specifici;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Azione", inversedBy="procedure", cascade={"all"})
     * @ORM\JoinTable(name="procedure_operative_azioni")
     *
     * @Assert\NotBlank()
     * @var Azione[]|Collection
     */
    protected $azioni;

    /**
     * @ORM\ManyToOne(targetEntity="TipoAmministrazioneEmittente")
     * @ORM\JoinColumn(name="amministrazione_emittente_id", referencedColumnName="id", nullable=true)
     *
     * @Assert\NotBlank()
     */
    protected $amministrazione_emittente;

    /**
     * @ORM\ManyToOne(targetEntity="TipoProceduraMonitoraggio")
     * @ORM\JoinColumn(name="tipo_procedura_monitoraggio_id", referencedColumnName="id", nullable=true)
     *
     * @Assert\NotBlank()
     */
    protected $tipo_procedura_monitoraggio;

    /**
     *
     * @ORM\ManyToMany(targetEntity="TipoOperazione", inversedBy="procedure", cascade={"all"})
     * @ORM\JoinTable(name="procedure_operative_tipi_operazioni")
     *
     * @Assert\NotBlank()
     */
    protected $tipi_operazioni;

    /**
     * @ORM\ManyToOne(targetEntity="TipoIter")
     * @ORM\JoinColumn(name="tipo_iter_id", referencedColumnName="id", nullable=true)
     *
     * @Assert\NotBlank()
     */
    protected $tipo_iter;

    /**
     * @ORM\ManyToOne(targetEntity="TipoFinanziamento")
     * @ORM\JoinColumn(name="tipo_finanziamento_id", referencedColumnName="id", nullable=true)
     *
     * @Assert\NotBlank()
     */
    protected $tipo_finanziamento;

    /**
     * @ORM\ManyToMany(targetEntity="TipoAiuto")
     *
     * @Assert\NotBlank()
     */
    protected $tipo_aiuto;

    /**
     * @ORM\Column(type="integer",  name="anno_programmazione", nullable=true)
     *
     * @Assert\NotBlank()
     */
    protected $anno_programmazione;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\SezioniAggiuntive", mappedBy="procedura")
     */
    protected $sezioni_aggiuntive;
    
    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\DnshProcedura", mappedBy="procedura")
     */
    protected $dnsh_procedura;

    /**
     * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\DocumentoProcedura", mappedBy="procedura")
     */
    protected $documenti;

    /**
     * @var boolean $marca_da_bollo
     * @ORM\Column(type="boolean", name="marca_da_bollo", nullable=true)
     */
    protected $marca_da_bollo;

    /**
     * @ORM\Column(type="string", length=25, name="tipologia_marca_da_bollo", nullable=true)
     */
    protected $tipologia_marca_da_bollo;

    /**
     * @var boolean $sezione_dati_generali
     * @ORM\Column(type="boolean", name="sezione_dati_generali", nullable=true)
     */
    protected $sezione_dati_generali;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Fase")
     * @ORM\JoinColumn(name="fase_id", referencedColumnName="id", nullable=false)
     */
    protected $fase;

    /**
     * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocollo", mappedBy="procedura")
     */
    protected $richieste_protocollo;

    /**
     * @ORM\OneToOne(targetEntity="SfingeBundle\Entity\ProceduraDatiCup", inversedBy="procedura")
     * @ORM\JoinColumn(name="dati_cup_id", referencedColumnName="id")
     */
    protected $procedura_dati_cup;

    /**
     * @var boolean $piano_costo_attivo
     * @ORM\Column(type="boolean", name="piano_costo_attivo", nullable=true)
     */
    protected $piano_costo_attivo;

    /**
     * @var boolean $multi_piano_costo
     * @ORM\Column(type="boolean", name="multi_piano_costo", nullable=true)
     */
    protected $multi_piano_costo;

    /**
     * @var boolean $modalita_finanziamento_attiva
     * @ORM\Column(type="boolean", name="modalita_finanziamento_attiva", nullable=true)
     */
    protected $modalita_finanziamento_attiva;

    /**
     * @var boolean $rating
     * @ORM\Column(type="boolean", name="rating", nullable=true)
     */
    protected $rating;

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
     * @var boolean $dati_incremento_occupazionale
     * @ORM\Column(type="boolean", name="dati_incremento_occupazionale", nullable=true)
     */
    protected $dati_incremento_occupazionale;

    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "sezione_video" )
     */
    protected $sezione_video;

    /**
     * @ORM\Column(type="string", length=25,  name="classifica", nullable=true)
     *
     */
    protected $classifica;

    /**
     * @ORM\Column(type="string", length=25,  name="fascicolo_principale", nullable=true)
     *
     */
    protected $fascicolo_principale;

    /**
     * @ORM\Column(type="string", length=4,  name="anno_protocollazione", nullable=true)
     *
     */
    protected $anno_protocollazione;

    /**
     * @ORM\Column(type="string", length=25,  name="unita_organizzativa", nullable=true)
     *
     */
    protected $unita_organizzativa;
    
    /**
     * @ORM\Column(type="string", length=50,  name="utente_robot", nullable=true)
     */
    protected $utente_robot;
    
    /**
     * @ORM\Column(type="string", length=50,  name="id_ute_in_utente_robot", nullable=true)
     */
    protected $IdUteInRobot;
    
    /**
     * @ORM\Column(type="string", length=50,  name="id_uo_in_utente_robot", nullable=true)
     */
    protected $idUoInRobot;

    /**
     * @ORM\Column(type="string", length=25,  name="classifica_rend", nullable=true)
     *
     */
    protected $classifica_rend;

    /**
     * @ORM\Column(type="string", length=25,  name="fascicolo_principale_rend", nullable=true)
     *
     */
    protected $fascicolo_principale_rend;

    /**
     * @ORM\Column(type="string", length=4,  name="anno_protocollazione_rend", nullable=true)
     *
     */
    protected $anno_protocollazione_rend;

    /**
     * @ORM\Column(type="string", length=25,  name="unita_organizzativa_rend", nullable=true)
     *
     */
    protected $unita_organizzativa_rend;
    
    /**
     * @ORM\Column(type="string", length=50,  name="utente_robot_rend", nullable=true)
     */
    protected $utente_robot_rend;
    
    /**
     * @ORM\Column(type="string", length=50,  name="id_ute_in_utente_robot_rend", nullable=true)
     */
    protected $IdUteInRendRobot;
    
    /**
     * @ORM\Column(type="string", length=50,  name="id_uo_in_utente_robot_rend", nullable=true)
     */
    protected $IdUoInRendRobot;
    
    /**
     * @ORM\Column(type="string", length=25,  name="classifica_ctrl", nullable=true)
     *
     */
    protected $classifica_ctrl;

    /**
     * @ORM\Column(type="string", length=25,  name="fascicolo_principale_ctrl", nullable=true)
     *
     */
    protected $fascicolo_principale_ctrl;

    /**
     * @ORM\Column(type="string", length=4,  name="anno_protocollazione_ctrl", nullable=true)
     *
     */
    protected $anno_protocollazione_ctrl;

    /**
     * @ORM\Column(type="string", length=25,  name="unita_organizzativa_ctrl", nullable=true)
     *
     */
    protected $unita_organizzativa_ctrl;
    
    /**
     * @ORM\Column(type="string", length=50,  name="utente_robot_ctrl", nullable=true)
     */
    protected $utente_robot_ctrl;
    
        /**
     * @ORM\Column(type="string", length=50,  name="id_ute_in_utente_robot_ctrl", nullable=true)
     */
    protected $IdUteInCtrlRobot;
    
    /**
     * @ORM\Column(type="string", length=50,  name="id_uo_in_utente_robot_ctrl", nullable=true)
     */
    protected $IdUoInCtrlRobot;
    
    /**
     * @var boolean $anticipo
     * @ORM\Column(type="boolean", name="anticipo", nullable=true)
     */
    protected $anticipo;

    /**
     * @var boolean $rimborso
     * @ORM\Column(type="boolean", name="rimborso", nullable=true)
     */
    protected $rimborso;

    /**
     * @var boolean $pagamento_soluzione_unica
     * @ORM\Column(type="boolean", name="pagamento_soluzione_unica", nullable=true)
     */
    protected $pagamento_soluzione_unica;

    /**
     * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\PermessiProcedura", mappedBy="procedura")
     */
    protected $permessi;

    /**
     * @ORM\Column(type="integer",  name="numero_richieste", nullable=true)
     * 
     * @Assert\NotBlank()
     */
    protected $numero_richieste;

    /**
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\FaseIstruttoria", mappedBy="procedura")
     */
    protected $fasi_istruttoria;

    /**
     * @var boolean $sportello
     * @ORM\Column(type="boolean", name="sportello", nullable=false)
     */
    protected $sportello;

    /**
     * @var boolean $sportello
     * @ORM\Column(type="boolean", name="visibile_in_corso", nullable=false)
     */
    protected $visibile_in_corso;

    /**
     * @var boolean $requisiti_rating
     * @ORM\Column(type="boolean", name="requisiti_rating", nullable=true)
     */
    protected $requisiti_rating;

    /**
     * @var boolean $stelle
     * @ORM\Column(type="boolean", name="stelle", nullable=true)
     */
    protected $stelle;

    /**
     * @var boolean $esenzione_marca_bollo
     * @ORM\Column(type="boolean", name="esenzione_marca_bollo", nullable=true)
     */
    protected $esenzione_marca_bollo;

    /**
     * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\Atto", mappedBy="procedura")
     */
    protected $atti;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura", mappedBy="procedura")
     */
    protected $modalita_pagamento;

    /**
     * @ORM\ManyToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento", mappedBy="procedura")
     * @var Collection|\AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento[]
     */
    protected $checklist_pagamento;

    /**
     * @ORM\ManyToMany(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ChecklistControllo", mappedBy="procedure")
     */
    protected $checklist_controllo;

    /**
     * @ORM\ManyToMany(targetEntity="AttuazioneControlloBundle\Entity\TipologiaGiustificativo", mappedBy="procedure")
     */
    protected $tipologie_giustificativo;
   
    /**
     *
     * @var boolean
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "rendicontazione_attiva" )
     */
    protected $rendicontazioneAttiva;
    
    /**
     * @var RendicontazioneProceduraConfig
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\RendicontazioneProceduraConfig", mappedBy="procedura")
     */
    protected $rendicontazioneProceduraConfig;     
     
    /**
     *
     * @ORM\OneToOne(targetEntity="MonitoraggioBundle\Entity\TC1ProceduraAttivazione")
     * @ORM\JoinColumn(name="mon_proc_att_id", referencedColumnName="id")
     * @var \MonitoraggioBundle\Entity\TC1ProceduraAttivazione
     */
    protected $mon_proc_att;
    
    /**
     *
     * @ORM\Column( type="string", nullable=true, length=30)
     * @Assert\Length( max = 30, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $mon_cod_aiuto_rna;
    
    
    /**
     * @ORM\Column( type="boolean", nullable=true, options={"default" : false})
     * @Assert\NotNull(groups={"monitoraggio"})
     */
    protected $mon_flag_aiuti;
    /**
     * @var boolean $abilita_reinvio_non_ammesse
     * @ORM\Column(type="boolean", name="abilita_reinvio_non_ammesse", nullable=true)
     */
    protected $abilita_reinvio_non_ammesse;
    
    /**
     *
     * @var Collection|ProgrammaProcedura[]
     * @ORM\OneToMany(targetEntity="ProgrammaProcedura", mappedBy="procedura", cascade={"persist"})
     */
    protected $mon_procedure_programmi;
    
    /**
     *
     * @var \RichiesteBundle\Entity\Richiesta[]|Collection
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\Richiesta", mappedBy="procedura")
     */
    protected $richieste;
    
    /**
     *
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ControlloProcedura", mappedBy="procedura")
     */
    protected $controlli;
    
    /**
    * @var float
    * @ORM\Column(name="mon_importo", nullable=true, type="decimal", precision=15, scale=2)
    */
    protected $mon_importo;


    /**
     * @var \DateTime|null mon_data_avvio_procedura
     *
     * @ORM\Column(name="mon_data_avvio_procedura", type="date", nullable=true)
     * 
     * @Assert\NotBlank(groups={"monitoraggio"})
     */
     protected $mon_data_avvio_procedura;
     
    /**
    * @var \DateTime|null mon_data_fine_procedura
    *
    * @ORM\Column(name="mon_data_fine_procedura", type="date", nullable=true)
    */
    protected $mon_data_fine_procedura;
             
    /**
     * @var \DateTime|null $data_approvazione
     *
     * @ORM\Column(name="data_approvazione", type="date", nullable=true)
     */
    protected $data_approvazione;
    
    /**
     * @var \DateTime|null $data_approvazione
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $data_avvio_lavori_preparatori;

    /**
     * @var \DateTime|null $data_approvazione
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $data_delibera;

    /**
     *
     * @var boolean
     * @ORM\Column(type="boolean", nullable= true, options={"default" : 1}, name= "proroga_attiva" )
     */
    protected $proroga_attiva;
    
    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $data_inoltro_pagamento;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $numero_massimo_richieste_procedura;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $attuale_finestra_temporale_presentazione;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 1}, name= "richiesta_firma_digitale" )
     */
    protected $richiesta_firma_digitale;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 1}, name= "richiesta_firma_digitale_step_successivi" )
     */
    protected $richiesta_firma_digitale_step_successivi;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 1}, name= "sezione_istruttoria_cup" )
     */
    protected $sezione_istruttoria_cup;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 1}, name= "sezione_istruttoria_nucleo" )
     */
    protected $sezione_istruttoria_nucleo;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=25,  name="centro_di_costo", nullable=true)
     */
    protected $centro_di_costo;

    /**
     * @var PropostaImpegno[]|Collection
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\PropostaImpegno", mappedBy="procedura")
     */
    protected $proposte_impegno;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente", inversedBy="rup_procedure")
     * @ORM\JoinColumn(name="rup_id", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank()
     */
    protected $rup;
    
    /**
     * Procedura constructor.
     */
    public function __construct() {
        $this->numero_proponenti = 1;
        $this->piano_costo_attivo = true;
        $this->modalita_finanziamento_attiva = true;
        $this->multi_piano_costo = false;
        $this->sezione_istruttoria_cup = 1;
        $this->sezione_istruttoria_nucleo = 1;

        $this->sezioni_aggiuntive = new ArrayCollection();
        $this->documenti = new ArrayCollection();
        $this->fascicoli_procedura = new ArrayCollection();
        $this->piani_costo = new ArrayCollection();
        $this->tipi_referenza = new ArrayCollection();
        $this->tipi_aiuto_proponente = new ArrayCollection();
        $this->azioni = new ArrayCollection();
        $this->richieste_protocollo = new ArrayCollection();
        $this->obiettivi_specifici = new ArrayCollection();
        $this->tipi_operazioni = new ArrayCollection();
        $this->fasi_istruttoria = new ArrayCollection();
        $this->atti = new ArrayCollection();
        $this->richieste = new ArrayCollection();
        $this->mon_procedure_programmi = new ArrayCollection();
        $this->controlli = new ArrayCollection();
        $this->modalita_pagamento = new ArrayCollection();
        $this->sezioni_piani_costo = new ArrayCollection();
        $this->tipo_aiuto = new ArrayCollection();
        
        $this->spese_ammissibili_forfettario = false;
        $this->spese_pubbliche_forfettario = false;
        $this->richiesta_firma_digitale = true;
        $this->richiesta_firma_digitale_step_successivi = true;
        $this->mostra_contatore_richieste_presentate = false;
    }

    /**
     * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\FascicoloProcedura", mappedBy="procedura")
     */
    protected $fascicoli_procedura;

    /**
     * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\FascicoloProceduraRendiconto", mappedBy="procedura")
     */
    protected $fascicoli_procedura_rendiconto;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $numero_proponenti;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\TipiReferenzaProcedura", mappedBy="procedura")
     */
    protected $tipi_referenza;
    
    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\TipoAiutoProponente", mappedBy="procedura")
     */
    protected $tipi_aiuto_proponente;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\SezionePianoCosto", mappedBy="procedura")
     */
    protected $sezioni_piani_costo;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\PianoCosto", mappedBy="procedura")
     * @var Collection|PianoCosto[]
     */
    protected $piani_costo;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\ModalitaFinanziamento", mappedBy="procedura")
     */
    protected $modalita_finanziamento;

    /**
     * @var boolean $fasi_procedurali
     * @ORM\Column(type="boolean", name="fasi_procedurali", nullable=true)
     */
    protected $fasi_procedurali;

    /**
     * @var boolean $fornitori
     * @ORM\Column(type="boolean", name="fornitori", nullable=true)
     */
    protected $fornitori;

    /**
     * @var boolean $aiuto_stato
     * @ORM\Column(type="boolean", name="aiuto_stato", nullable=true)
     */
    protected $aiuto_stato;

    /**
     * @ORM\Column(type="string", length=255,  name="codice_cci", nullable=true)
     *
     */
    protected $codice_cci = '2014IT16RFOP008';

    /**
     * @ORM\ManyToOne(targetEntity="PrioritaProcedura")
     * @ORM\JoinColumn(name="priorita_procedura_id", referencedColumnName="id", nullable=true)
     *
     * @Assert\NotBlank()
     */
    protected $priorita_procedura;

    /**
     * @ORM\Column(type="string", length=255,  name="fondo", nullable=true)
     *
     */
    protected $fondo = 'FESR';

    /**
     * @ORM\Column(type="string", length=255,  name="categoria_regione", nullable=true)
     *
     */
    protected $categoria_regione = 'Regioni più sviluppate';

    /**
     * @ORM\Column(type="string", length=255,  name="organismo", nullable=true)
     *
     */
    protected $organismo = 'Autorità di Gestione';

    /**
     * @ORM\OneToMany(targetEntity="DocumentoBundle\Entity\TipologiaDocumento", mappedBy="procedura")
     */
    protected $documenti_richiesti;

    /**
     * @ORM\ManyToMany(targetEntity="AttuazioneControlloBundle\Entity\TipologiaQuietanza")
     * @ORM\JoinTable(name="procedure_operative_tipologie_quietanze")
     * @ORM\OrderBy({"descrizione" = "ASC"})
     */
    protected $tipologie_quietanze;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC2TipoProceduraAttivazione")
     * @ORM\JoinColumn(name="mon_tipo_procedura_attivazione", referencedColumnName="id", nullable=true)
     * @var \MonitoraggioBundle\Entity\TC2TipoProceduraAttivazione
     */
    protected $mon_tipo_procedura_attivazione;

    /**
     * @ORM\Column(type="string", nullable = true, length = 30 )
     * @Assert\Length( max=30, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string|null mon_codice_procedura_attivazione
     */
    protected $mon_codice_procedura_attivazione;
    
    /**
     * @var boolean $sede_montana
     * @ORM\Column(type="boolean", name="sede_montana", nullable=true)
     */
    protected $sede_montana;
    
    /**
     * @var boolean $risorse_progetto
     * @ORM\Column(type="boolean", name="risorse_progetto", nullable=true)
     */
    protected $risorse_progetto;
    
    /**

     * @var boolean $comportamento_particolare_variazione
     * @ORM\Column(type="boolean", name="comportamento_particolare_variazione", nullable=true)
     * 
     * Serve per gestire i bandi per i quali la variazione diventa efficate in base alla 
     * data di validazione della checklist piustosto che dalla data di invio pagamento
     */
    protected $comportamento_particolare_variazione;

    /**
     * @var boolean $ammette_piva_cf_duplicati
     * @ORM\Column(type="boolean", name="ammette_piva_cf_duplicati", nullable=true)
     */
    protected $ammette_piva_cf_duplicati;

    
    /**
     * @var boolean $generatore_entrate
     * @ORM\Column(type="boolean", name="generatore_entrate", nullable=true)
     */
    protected $generatore_entrate;
    
    /**
     * @ORM\Column(type="string", length=50,  name="registro_prot", nullable=true)
     * VALORI POSSIBILI PERORA SONO 'CR' E 'PG'
     * CR: protocollazioni ordinanza sisma
     * PG: tutto il resto fino ad ordine contrario
     */
    protected $registro_protocollazione;
    
    /**
     * @ORM\Column(type="string", length=50,  name="servizio_protocollazione", nullable=true)
     * VALORI POSSIBILI PERORA SONO 'CR' E 'PG'
     * REGISTRAZIONE: protocollazioni ordinanza sisma chiama la 'registraById' della protocollazione con relativo servizio 'RegistrazioneDocERService'
     * INTEGRAZIONE: tutto il resto fino ad ordine contrario chiama la 'protocollaById' della protocollazione con relativo servizio 'IntegrazioneDocERService'
     */
    protected $servizio_protocollazione;

    /**
     * @var string
     * @ORM\Column(type="string", length=20, nullable=false, options={"default": "CURRENT_TIMESTAMP"})
     */
    protected $mon_tipo_beneficiario = self::MON_TIPO_PRG_MISTO;
    
    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0} )
     */
    protected $spese_ammissibili_forfettario;
    
    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0} )
     */
    protected $spese_pubbliche_forfettario;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "mostra_contatore_richieste_presentate" )
     */
    protected $mostra_contatore_richieste_presentate;

    /**
     * @ORM\Column(type="boolean", name="sezione_ambiti_tematici_s3", nullable=true)
     */
    protected $sezione_ambiti_tematici_s3;

    /**
     * @ORM\Column(type="boolean", name="ambiti_tematici_s3_multipli", nullable=true)
     */
    protected $ambiti_tematici_s3_multipli;

    /**
     * @ORM\Column(type="boolean", name="ambiti_tematici_s3_descrizione_descrittori", nullable=true)
     */
    protected $ambiti_tematici_s3_descrizione_descrittori;

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getResponsabile() {
        return $this->responsabile;
    }

    /**
     * @param mixed $responsabile
     */
    public function setResponsabile($responsabile) {
        $this->responsabile = $responsabile;
    }

    /**
     * @return mixed
     */
    public function getStatoProcedura() {
        return $this->stato_procedura;
    }

    /**
     * @param mixed $stato_procedura
     */
    public function setStatoProcedura($stato_procedura) {
        $this->stato_procedura = $stato_procedura;
    }

    /**
     * @return mixed
     */
    public function getAtto() {
        return $this->atto;
    }

    /**
     * @param mixed $atto
     */
    public function setAtto($atto) {
        $this->atto = $atto;
    }

    /**
     * @return mixed
     */
    public function getTitolo() {
        return $this->titolo;
    }

    /**
     * @param mixed $titolo
     */
    public function setTitolo($titolo) {
        $this->titolo = $titolo;
    }

    /**
     * @return mixed
     */
    public function getRisorseDisponibili() {
        return $this->risorse_disponibili;
    }

    /**
     * @param mixed $risorse_disponibili
     */
    public function setRisorseDisponibili($risorse_disponibili) {
        $this->risorse_disponibili = $risorse_disponibili;
    }

    /**
     * @return Asse
     */
    public function getAsse() {
        return $this->asse;
    }

    /**
     * @param mixed $asse
     */
    public function setAsse($asse) {
        $this->asse = $asse;
    }

    /**
     * @return Azione[]|Collection
     */
    public function getAzioni() {
        return $this->azioni;
    }

    /**
     * @param mixed $azioni
     */
    public function setAzioni($azioni) {
        $this->azioni = $azioni;
    }

    /**
     * @param mixed $azione
     */
    public function addAzioni($azione) {
        $azione->addProcedure($this);
        $this->azioni->add($azione);
    }

    /**
     * @return mixed
     */
    public function getAmministrazioneEmittente() {
        return $this->amministrazione_emittente;
    }

    /**
     * @param mixed $amministrazione_emittente
     */
    public function setAmministrazioneEmittente($amministrazione_emittente) {
        $this->amministrazione_emittente = $amministrazione_emittente;
    }

    /**
     * @return mixed
     */
    public function getTipoProceduraMonitoraggio() {
        return $this->tipo_procedura_monitoraggio;
    }

    /**
     * @param mixed $tipo_procedura_monitoraggio
     */
    public function setTipoProceduraMonitoraggio($tipo_procedura_monitoraggio) {
        $this->tipo_procedura_monitoraggio = $tipo_procedura_monitoraggio;
    }

    /**
     * @return mixed
     */
    public function getTipiOperazioni() {
        return $this->tipi_operazioni;
    }

    /**
     * @param mixed $tipi_operazioni
     */
    public function setTipiOperazioni($tipi_operazioni) {
        $this->tipi_operazioni = $tipi_operazioni;
    }

    /**
     * @param mixed $tipo_operazione
     */
    public function addTipiOperazioni($tipo_operazione) {
        $tipo_operazione->addProcedure($this);
        $this->tipi_operazioni->add($tipo_operazione);
    }

    public function getTipoIter(): ?TipoIter {
        return $this->tipo_iter;
    }

    public function setTipoIter(?TipoIter $tipo_iter) {
        $this->tipo_iter = $tipo_iter;
    }

    /**
     * @return mixed
     */
    public function getTipoFinanziamento() {
        return $this->tipo_finanziamento;
    }

    /**
     * @param mixed $tipo_finanziamento
     */
    public function setTipoFinanziamento($tipo_finanziamento) {
        $this->tipo_finanziamento = $tipo_finanziamento;
    }

    /**
     * @return Collection|TipoAiuto[]
     */
    public function getTipoAiuto(): Collection {
        return $this->tipo_aiuto;
    }

    /**
     * @param Collection|TipoAiuto[] $tipo_aiuto
     */
    public function setTipoAiuto(Collection $tipo_aiuto) {
        $this->tipo_aiuto = $tipo_aiuto;
    }

    /**
     * @return mixed
     */
    public function getAnnoProgrammazione() {
        return $this->anno_programmazione;
    }

    /**
     * @param mixed $anno_programmazione
     */
    public function setAnnoProgrammazione($anno_programmazione) {
        $this->anno_programmazione = $anno_programmazione;
    }

    function getSezioniAggiuntive() {
        return $this->sezioni_aggiuntive;
    }

    function setSezioniAggiuntive($sezioni_aggiuntive) {
        $this->sezioni_aggiuntive = $sezioni_aggiuntive;
    }

    /**
     * @return mixed
     */
    public function getNumeroProponenti() {
        return $this->numero_proponenti;
    }

    /**
     * @param mixed $numero_proponenti
     */
    public function setNumeroProponenti($numero_proponenti) {
        $this->numero_proponenti = $numero_proponenti;
    }

    /**
     * @return ArrayCollection
     */
    public function getFascicoliProcedura() {
        return $this->fascicoli_procedura;
    }

    /**
     * @param $fascicoli_procedura
     */
    public function setFascicoliProcedura($fascicoli_procedura) {
        $this->fascicoli_procedura = $fascicoli_procedura;
    }

    /**
     * @return mixed
     */
    public function getDocumenti() {
        return $this->documenti;
    }

    /**
     * @param mixed $documenti
     */
    public function setDocumenti($documenti) {
        $this->documenti = $documenti;
    }

    /**
     * @return mixed
     */
    public function getTipiReferenza() {
        return $this->tipi_referenza;
    }

    /**
     * @param mixed $tipi_referenza
     */
    public function setTipiReferenza($tipi_referenza) {
        $this->tipi_referenza = $tipi_referenza;
    }

    /**
     * @return \RichiesteBundle\Entity\SezionePianoCosto[]|Collection
     */
    public function getSezioniPianiCosto(): Collection {
        return $this->sezioni_piani_costo;
    }

    public function setSezioniPianiCosto(Collection $sezioni_piani_costo) {
        $this->sezioni_piani_costo = $sezioni_piani_costo;
    }

    /**
     * @return Collection|PianoCosto[]
     */
    public function getPianiCosto(): Collection {
        return $this->piani_costo;
    }


    public function setPianiCosto(Collection $piani_costo) {
        $this->piani_costo = $piani_costo;
    }

    /**
     * @return boolean
     */
    public function isMarcaDaBollo() {
        return $this->marca_da_bollo;
    }

    /**
     * @param boolean $marca_da_bollo
     */
    public function setMarcaDaBollo($marca_da_bollo) {
        $this->marca_da_bollo = $marca_da_bollo;
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
     * @return boolean
     */
    public function isSezioneDatiGenerali() {
        return $this->sezione_dati_generali;
    }

    /**
     * @param boolean $sezione_dati_generali
     */
    public function setSezioneDatiGenerali($sezione_dati_generali) {
        $this->sezione_dati_generali = $sezione_dati_generali;
    }

    public function getTipoProcedura() {
        if ($this instanceof ManifestazioneInteresse) {
            return "Manifestazione d'interesse";
        }
        if ($this instanceof Bando) {
            return "Bando";
        }
        if ($this instanceof AssistenzaTecnica) {
            return "Assistenza tecnica";
        }
        if ($this instanceof IngegneriaFinanziaria) {
            return "Ingegneria finanziaria";
        }
    }

    public function __toString() {
        return $this->titolo;
    }

    /**
     * @param ExecutionContextInterface $context
     * Callback per verificare le azioni.
     */
    public function checkAzioni(ExecutionContextInterface $context) {
        if (count($this->getAzioni()) == 0) {
            $context->buildViolation("Selezionare almeno un'azione")
                    ->atPath('azioni')
                    ->addViolation();
        }
    }

    /**
     * @param ExecutionContextInterface $context
     * Callback per verificare gli obiettivi specifici.
     */
    public function checkObiettiviSpecifici(ExecutionContextInterface $context) {
        if (count($this->getObiettiviSpecifici()) == 0) {
            $context->buildViolation("Selezionare almeno un obiettivo specifico")
                    ->atPath('obiettivi_specifici')
                    ->addViolation();
        }
    }

    /**
     * Get marcaDaBollo
     *
     * @return boolean
     */
    public function getMarcaDaBollo() {
        return $this->marca_da_bollo;
    }

    /**
     * Get sezioneDatiGenerali
     *
     * @return boolean
     */
    public function getSezioneDatiGenerali() {
        return $this->sezione_dati_generali;
    }

    /**
     * Remove azioni
     *
     * @param \SfingeBundle\Entity\Azione $azioni
     */
    public function removeAzioni(\SfingeBundle\Entity\Azione $azioni) {
        $this->azioni->removeElement($azioni);
    }

    /**
     * Add sezioniAggiuntive
     *
     * @param \RichiesteBundle\Entity\SezioniAggiuntive $sezioniAggiuntive
     *
     * @return Procedura
     */
    public function addSezioniAggiuntive(\RichiesteBundle\Entity\SezioniAggiuntive $sezioniAggiuntive) {
        $this->sezioni_aggiuntive[] = $sezioniAggiuntive;

        return $this;
    }

    /**
     * Remove sezioniAggiuntive
     *
     * @param \RichiesteBundle\Entity\SezioniAggiuntive $sezioniAggiuntive
     */
    public function removeSezioniAggiuntive(\RichiesteBundle\Entity\SezioniAggiuntive $sezioniAggiuntive) {
        $this->sezioni_aggiuntive->removeElement($sezioniAggiuntive);
    }

    /**
     * Add documenti
     *
     * @param \SfingeBundle\Entity\DocumentoProcedura $documenti
     *
     * @return Procedura
     */
    public function addDocumenti(\SfingeBundle\Entity\DocumentoProcedura $documenti) {
        $this->documenti[] = $documenti;

        return $this;
    }

    /**
     * Remove documenti
     *
     * @param \SfingeBundle\Entity\DocumentoProcedura $documenti
     */
    public function removeDocumenti(\SfingeBundle\Entity\DocumentoProcedura $documenti) {
        $this->documenti->removeElement($documenti);
    }

    /**
     * Set fase
     *
     * @param \SfingeBundle\Entity\Fase $fase
     *
     * @return Procedura
     */
    public function setFase(\SfingeBundle\Entity\Fase $fase = null) {
        $this->fase = $fase;

        return $this;
    }

    /**
     * Get fase
     *
     * @return \SfingeBundle\Entity\Fase
     */
    public function getFase() {
        return $this->fase;
    }

    /**
     * Get richieste_protocollo
     *
     * @return \SfingeBundle\Entity\Fase
     */
    function getRichieste_protocollo() {
        return $this->richieste_protocollo;
    }

    /**
     * Set richieste_protocollo
     *
     * @param \SfingeBundle\Entity\Fase $fase
     *
     * @return Procedura
     */
    function setRichieste_protocollo($richieste_protocollo) {
        $this->richieste_protocollo = $richieste_protocollo;
    }

    /**
     * Add fascicoliProcedura
     *
     * @param \SfingeBundle\Entity\FascicoloProcedura $fascicoliProcedura
     *
     * @return Procedura
     */
    public function addFascicoliProcedura(\SfingeBundle\Entity\FascicoloProcedura $fascicoliProcedura) {
        $this->fascicoli_procedura[] = $fascicoliProcedura;

        return $this;
    }

    /**
     * Remove fascicoliProcedura
     *
     * @param \SfingeBundle\Entity\FascicoloProcedura $fascicoliProcedura
     */
    public function removeFascicoliProcedura(\SfingeBundle\Entity\FascicoloProcedura $fascicoliProcedura) {
        $this->fascicoli_procedura->removeElement($fascicoliProcedura);
    }

    /**
     * Add tipiReferenza
     *
     * @param \RichiesteBundle\Entity\TipiReferenzaProcedura $tipiReferenza
     *
     * @return Procedura
     */
    public function addTipiReferenza(\RichiesteBundle\Entity\TipiReferenzaProcedura $tipiReferenza) {
        $this->tipi_referenza[] = $tipiReferenza;

        return $this;
    }

    /**
     * Remove tipiReferenza
     *
     * @param \RichiesteBundle\Entity\TipiReferenzaProcedura $tipiReferenza
     */
    public function removeTipiReferenza(\RichiesteBundle\Entity\TipiReferenzaProcedura $tipiReferenza) {
        $this->tipi_referenza->removeElement($tipiReferenza);
    }
    
    public function addTipiAiutoProponente(\RichiesteBundle\Entity\TipoAiutoProponente $tipiAiutoProponente) {
        $this->tipi_aiuto_proponente[] = $tipiAiutoProponente;

        return $this;
    }

    public function removeTipiAiutoProponente(\RichiesteBundle\Entity\TipoAiutoProponente $tipiAiutoProponente) {
        $this->tipi_aiuto_proponente->removeElement($tipiAiutoProponente);
    }

    /**
     * Add pianiCosto
     *
     * @param \RichiesteBundle\Entity\PianoCosto $pianiCosto
     *
     * @return Procedura
     */
    public function addPianiCosto(\RichiesteBundle\Entity\PianoCosto $pianiCosto) {
        $this->piani_costo[] = $pianiCosto;

        return $this;
    }

    /**
     * Remove pianiCosto
     *
     * @param \RichiesteBundle\Entity\PianoCosto $pianiCosto
     */
    public function removePianiCosto(\RichiesteBundle\Entity\PianoCosto $pianiCosto) {
        $this->piani_costo->removeElement($pianiCosto);
    }

    public function getProceduraDatiCup() {
        return $this->procedura_dati_cup;
    }

    public function setProceduraDatiCup($procedura_dati_cup) {
        $this->procedura_dati_cup = $procedura_dati_cup;
    }

    public function getPianoCostoAttivo() {
        return $this->piano_costo_attivo;
    }

    public function getMultiPianoCosto() {
        return $this->multi_piano_costo;
    }

    public function setPianoCostoAttivo($piano_costo_attivo) {
        $this->piano_costo_attivo = $piano_costo_attivo;
    }

    public function setMultiPianoCosto($multi_piano_costo) {
        $this->multi_piano_costo = $multi_piano_costo;
    }

    /**
     * @return boolean
     */
    public function isRequisitiRating() {
        return $this->requisiti_rating;
    }

    /**
     * @return boolean
     */
    public function isRating() {
        return $this->rating;
    }

    /**
     * @param boolean $rating
     */
    public function setRating($rating) {
        $this->rating = $rating;
    }

    /**
     * @return boolean
     */
    public function isFemminile() {
        return $this->femminile;
    }

    /**
     * @param boolean $femminile
     */
    public function setFemminile($femminile) {
        $this->femminile = $femminile;
    }

    /**
     * @return boolean
     */
    public function isGiovanile() {
        return $this->giovanile;
    }

    /**
     * @param boolean $giovanile
     */
    public function setGiovanile($giovanile) {
        $this->giovanile = $giovanile;
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
     * @return bool
     */
    public function isDatiIncrementoOccupazionale(): ?bool
    {
        return $this->dati_incremento_occupazionale;
    }

    /**
     * @param bool $dati_incremento_occupazionale
     */
    public function setDatiIncrementoOccupazionale(bool $dati_incremento_occupazionale): void
    {
        $this->dati_incremento_occupazionale = $dati_incremento_occupazionale;
    }

    /**
     * @return mixed
     */
    public function getSezioneVideo()
    {
        return $this->sezione_video;
    }

    /**
     * @param mixed $sezione_video
     */
    public function setSezioneVideo($sezione_video): void
    {
        $this->sezione_video = $sezione_video;
    }

    /**
     * Get classifica
     *
     * @return string
     */
    function getClassifica() {
        return $this->classifica;
    }

    /**
     * Get fascicolo_principale
     *
     * @return string
     */
    function getFascicolo_principale() {
        return $this->fascicolo_principale;
    }

    /**
     * Get anno_protocollazione
     *
     * @return string
     */
    public function getAnnoProtocollazione() {
        return $this->anno_protocollazione;
    }

    /**
     * Get unita_organizzativa
     *
     * @return string
     */
    function getUnita_organizzativa() {
        return $this->unita_organizzativa;
    }

    /**
     * Set classifica
     *
     * @param string $classifica
     *
     * @return Procedura
     */
    function setClassifica($classifica) {
        $this->classifica = $classifica;
    }

    /**
     * Set fascicolo_principale
     *
     * @param string $fascicolo_principale
     *
     * @return Procedura
     */
    function setFascicolo_principale($fascicolo_principale) {
        $this->fascicolo_principale = $fascicolo_principale;
    }

    /**
     * Set anno_protocollazione
     *
     * @param string $anno_protocollazione
     *
     * @return Procedura
     */
    public function setAnnoProtocollazione($anno_protocollazione) {
        $this->anno_protocollazione = $anno_protocollazione;
    }

    /**
     * Set unita_organizzativa
     *
     * @param string $unita_organizzativa
     *
     * @return Procedura
     */
    function setUnita_organizzativa($unita_organizzativa) {
        $this->unita_organizzativa = $unita_organizzativa;
    }

    /**
     * @return boolean
     */
    public function isAnticipo() {
        return $this->anticipo;
    }

    /**
     * @param boolean $anticipo
     */
    public function setAnticipo($anticipo) {
        $this->anticipo = $anticipo;
    }

    /**
     * @return boolean
     */
    public function isRimborso() {
        return $this->rimborso;
    }

    /**
     * @param boolean $rimborso
     */
    public function setRimborso($rimborso) {
        $this->rimborso = $rimborso;
    }

    /**
     * @return boolean
     */
    public function isPagamentoSoluzioneUnica() {
        return $this->pagamento_soluzione_unica;
    }

    /**
     * @param boolean $pagamento_soluzione_unica
     */
    public function setPagamentoSoluzioneUnica($pagamento_soluzione_unica) {
        $this->pagamento_soluzione_unica = $pagamento_soluzione_unica;
    }

    /**
     * @return mixed
     */
    public function getObiettiviSpecifici() {
        return $this->obiettivi_specifici;
    }

    /**
     * @param mixed $obiettivi_specifici
     */
    public function setObiettiviSpecifici($obiettivi_specifici) {
        $this->obiettivi_specifici = $obiettivi_specifici;
    }

    /**
     * @param mixed $obiettivo_specifico
     */
    public function addObiettiviSpecifici($obiettivo_specifico) {
        $obiettivo_specifico->addProcedure($this);
        $this->obiettivi_specifici->add($obiettivo_specifico);
    }

    /**
     * Remove obiettivi_specifici
     *
     * @param \SfingeBundle\Entity\ObiettivoSpecifico $obiettivi_specifici
     */
    public function removeObiettiviSpecifici(\SfingeBundle\Entity\ObiettivoSpecifico $obiettivi_specifici) {
        $this->azioni->removeElement($obiettivi_specifici);
    }

    /**
     * @return boolean
     */
    public function isFasiProcedurali() {
        return $this->fasi_procedurali == true;
    }

    /**
     * @param boolean $fasi_procedurali
     */
    public function setFasiProcedurali($fasi_procedurali) {
        $this->fasi_procedurali = $fasi_procedurali;
    }

    public function getFasiProcedurali() {
        return $this->fasi_procedurali;
    }

    public function getProcedura() {
        return $this;
    }

    public function getModalitaFinanziamento() {
        return $this->modalita_finanziamento;
    }

    public function setModalitaFinanziamento($modalita_finanziamento) {
        $this->modalita_finanziamento = $modalita_finanziamento;
    }

    public function getModalitaFinanziamentoAttiva() {
        return $this->modalita_finanziamento_attiva;
    }

    public function setModalitaFinanziamentoAttiva($modalita_finanziamento_attiva) {
        $this->modalita_finanziamento_attiva = $modalita_finanziamento_attiva;
    }

    public function getNumeroRichieste() {
        return $this->numero_richieste;
    }

    public function setNumeroRichieste($numero_richieste) {
        $this->numero_richieste = $numero_richieste;
    }

    function getSportello() {
        return $this->sportello;
    }

    function setSportello($sportello) {
        $this->sportello = $sportello;
    }

    public function getVisibileInCorso() {
        return $this->visibile_in_corso;
    }

    public function setVisibileInCorso($visibile_in_corso) {
        $this->visibile_in_corso = $visibile_in_corso;
    }

    function getFasiIstruttoria() {
        return $this->fasi_istruttoria;
    }

    function setFasiIstruttoria($fasi_istruttoria) {
        $this->fasi_istruttoria = $fasi_istruttoria;
    }

    function getAtti() {
        return $this->atti;
    }

    function setAtti($atti) {
        $this->atti = $atti;
    }

    public function getFascicoliProceduraRendiconto() {
        return $this->fascicoli_procedura_rendiconto;
    }

    public function setFascicoliProceduraRendiconto($fascicoli_procedura_rendiconto) {
        $this->fascicoli_procedura_rendiconto = $fascicoli_procedura_rendiconto;
    }

    /**
     * @return \AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura[]|Collection
     */
    public function getModalitaPagamento() {
        return $this->modalita_pagamento;
    }

    public function setModalitaPagamento(Collection $modalita_pagamento) {
        $this->modalita_pagamento = $modalita_pagamento;
        return $this;
    }

    /**
     * @return Collection|\AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento[]
     */
    function getChecklistPagamento():Collection {
        return $this->checklist_pagamento;
    }

    function setChecklistPagamento($checklist_pagamento) {
        $this->checklist_pagamento = $checklist_pagamento;
        return $this;
    }

    /**
     * Get rating
     *
     * @return boolean 
     */
    public function getRating() {
        return $this->rating;
    }

    /**
     * Get femminile
     *
     * @return boolean 
     */
    public function getFemminile() {
        return $this->femminile;
    }

    /**
     * Get giovanile
     *
     * @return boolean 
     */
    public function getGiovanile() {
        return $this->giovanile;
    }

    /**
     * Set fascicolo_principale
     *
     * @param string $fascicoloPrincipale
     * @return Procedura
     */
    public function setFascicoloPrincipale($fascicoloPrincipale) {
        $this->fascicolo_principale = $fascicoloPrincipale;

        return $this;
    }

    /**
     * Get fascicolo_principale
     *
     * @return string 
     */
    public function getFascicoloPrincipale() {
        return $this->fascicolo_principale;
    }

    /**
     * Set unita_organizzativa
     *
     * @param string $unitaOrganizzativa
     * @return Procedura
     */
    public function setUnitaOrganizzativa($unitaOrganizzativa) {
        $this->unita_organizzativa = $unitaOrganizzativa;

        return $this;
    }

    /**
     * Get unita_organizzativa
     *
     * @return string 
     */
    public function getUnitaOrganizzativa() {
        return $this->unita_organizzativa;
    }

    /**
     * Get anticipo
     *
     * @return boolean 
     */
    public function getAnticipo() {
        return $this->anticipo;
    }

    /**
     * Get rimborso
     *
     * @return boolean 
     */
    public function getRimborso() {
        return $this->rimborso;
    }

    /**
     * Get pagamento_soluzione_unica
     *
     * @return boolean 
     */
    public function getPagamentoSoluzioneUnica() {
        return $this->pagamento_soluzione_unica;
    }

    /**
     * Set fornitori
     *
     * @param boolean $fornitori
     * @return Procedura
     */
    public function setFornitori($fornitori) {
        $this->fornitori = $fornitori;

        return $this;
    }

    /**
     * Get fornitori
     *
     * @return boolean 
     */
    public function getFornitori() {
        return $this->fornitori;
    }

    /**
     * Remove tipi_operazioni
     *
     * @param \SfingeBundle\Entity\TipoOperazione $tipiOperazioni
     */
    public function removeTipiOperazioni(\SfingeBundle\Entity\TipoOperazione $tipiOperazioni) {
        $this->tipi_operazioni->removeElement($tipiOperazioni);
    }

    /**
     * Add richieste_protocollo
     *
     * @param \ProtocollazioneBundle\Entity\RichiestaProtocollo $richiesteProtocollo
     * @return Procedura
     */
    public function addRichiesteProtocollo(RichiestaProtocollo $richiesteProtocollo) {
        $this->richieste_protocollo[] = $richiesteProtocollo;

        return $this;
    }

    /**
     * Remove richieste_protocollo
     *
     * @param \ProtocollazioneBundle\Entity\RichiestaProtocollo $richiesteProtocollo
     */
    public function removeRichiesteProtocollo(RichiestaProtocollo $richiesteProtocollo) {
        $this->richieste_protocollo->removeElement($richiesteProtocollo);
    }

    /**
     * Get richieste_protocollo
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRichiesteProtocollo() {
        return $this->richieste_protocollo;
    }

    /**
     * Add permessi
     *
     * @param \SfingeBundle\Entity\PermessiProcedura $permessi
     * @return Procedura
     */
    public function addPermessi(PermessiProcedura $permessi) {
        $this->permessi[] = $permessi;

        return $this;
    }

    /**
     * Remove permessi
     *
     * @param \SfingeBundle\Entity\PermessiProcedura $permessi
     */
    public function removePermessi(PermessiProcedura $permessi) {
        $this->permessi->removeElement($permessi);
    }

    /**
     * Get permessi
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPermessi() {
        return $this->permessi;
    }

    /**
     * Add fasi_istruttoria
     *
     * @param \IstruttorieBundle\Entity\FaseIstruttoria $fasiIstruttoria
     * @return Procedura
     */
    public function addFasiIstruttorium(\IstruttorieBundle\Entity\FaseIstruttoria $fasiIstruttoria) {
        $this->fasi_istruttoria[] = $fasiIstruttoria;

        return $this;
    }

    /**
     * Remove fasi_istruttoria
     *
     * @param \IstruttorieBundle\Entity\FaseIstruttoria $fasiIstruttoria
     */
    public function removeFasiIstruttorium(\IstruttorieBundle\Entity\FaseIstruttoria $fasiIstruttoria) {
        $this->fasi_istruttoria->removeElement($fasiIstruttoria);
    }

    /**
     * Add modalita_finanziamento
     *
     * @param \RichiesteBundle\Entity\ModalitaFinanziamento $modalitaFinanziamento
     * @return Procedura
     */
    public function addModalitaFinanziamento(\RichiesteBundle\Entity\ModalitaFinanziamento $modalitaFinanziamento) {
        $this->modalita_finanziamento[] = $modalitaFinanziamento;

        return $this;
    }

    /**
     * Remove modalita_finanziamento
     *
     * @param \RichiesteBundle\Entity\ModalitaFinanziamento $modalitaFinanziamento
     */
    public function removeModalitaFinanziamento(\RichiesteBundle\Entity\ModalitaFinanziamento $modalitaFinanziamento) {
        $this->modalita_finanziamento->removeElement($modalitaFinanziamento);
    }

    /**
     * Set requisiti_rating
     *
     * @param boolean $requisitiRating
     * @return Procedura
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

    /**
     * Set stelle
     *
     * @param boolean $stelle
     * @return Procedura
     */
    public function setStelle($stelle) {
        $this->stelle = $stelle;

        return $this;
    }

    /**
     * Get stelle
     *
     * @return boolean 
     */
    public function getStelle() {
        return $this->stelle;
    }

    /**
     * Set esenzione_marca_bollo
     *
     * @param boolean $esenzioneMarcaBollo
     * @return Procedura
     */
    public function setEsenzioneMarcaBollo($esenzioneMarcaBollo) {
        $this->esenzione_marca_bollo = $esenzioneMarcaBollo;

        return $this;
    }

    /**
     * Get esenzione_marca_bollo
     *
     * @return boolean 
     */
    public function getEsenzioneMarcaBollo() {
        return $this->esenzione_marca_bollo;
    }

    public function getChecklistControllo() {
        return $this->checklist_controllo;
    }

    public function setChecklistControllo($checklist_controllo) {
        $this->checklist_controllo = $checklist_controllo;
        return $this;
    }

    function getAiutoStato() {
        return $this->aiuto_stato;
    }

    function getCodiceCci() {
        return $this->codice_cci;
    }

    function setAiutoStato($aiuto_stato) {
        $this->aiuto_stato = $aiuto_stato;
    }

    function setCodiceCci($codice_cci) {
        $this->codice_cci = $codice_cci;
    }

    public function setFondo(?string $fondo): self {
        $this->fondo = $fondo;

        return $this;
    }

    /**
     * Get fondo
     *
     * @return string 
     */
    public function getFondo() {
        return $this->fondo;
    }

    public function addAtti(Atto $atti): self {
        $this->atti[] = $atti;

        return $this;
    }

    public function addModalitaPagamento(ModalitaPagamentoProcedura $modalitaPagamento): self {
        $this->modalita_pagamento[] = $modalitaPagamento;

        return $this;
    }

    public function removeModalitaPagamento(ModalitaPagamentoProcedura $modalitaPagamento): void {
        $this->modalita_pagamento->removeElement($modalitaPagamento);
    }

    public function addChecklistPagamento(ChecklistPagamento $checklistPagamento): self {
        $this->checklist_pagamento[] = $checklistPagamento;

        return $this;
    }

    public function removeChecklistPagamento(ChecklistPagamento $checklistPagamento): void {
        $this->checklist_pagamento->removeElement($checklistPagamento);
    }

    public function addChecklistControllo(ChecklistControllo $checklistControllo): self {
        $this->checklist_controllo[] = $checklistControllo;

        return $this;
    }

    public function removeChecklistControllo(ChecklistControllo $checklistControllo): void {
        $this->checklist_controllo->removeElement($checklistControllo);
    }

    public function addFascicoliProceduraRendiconto(FascicoloProceduraRendiconto $fascicoliProceduraRendiconto): self {
        $this->fascicoli_procedura_rendiconto[] = $fascicoliProceduraRendiconto;

        return $this;
    }

    public function removeFascicoliProceduraRendiconto(FascicoloProceduraRendiconto $fascicoliProceduraRendiconto): void {
        $this->fascicoli_procedura_rendiconto->removeElement($fascicoliProceduraRendiconto);
    }

    /**
     * @param string $categoriaRegione
     * @return Procedura
     */
    public function setCategoriaRegione($categoriaRegione): self {
        $this->categoria_regione = $categoriaRegione;

        return $this;
    }

    /**
     * @return string 
     */
    public function getCategoriaRegione() {
        return $this->categoria_regione;
    }

    /**
     * @param string $organismo
     * @return Procedura
     */
    public function setOrganismo($organismo): self {
        $this->organismo = $organismo;

        return $this;
    }

    /**
     * @return string 
     */
    public function getOrganismo() {
        return $this->organismo;
    }

    /**
     * @return \SfingeBundle\Entity\PrioritaProcedura 
     */
    public function getPrioritaProcedura() {
        return $this->priorita_procedura;
    }

    public function getTipologieQuietanze() {
        return $this->tipologie_quietanze;
    }

    public function setTipologieQuietanze($tipologie_quietanze) {
        $this->tipologie_quietanze = $tipologie_quietanze;
    }

    public function getTipologieGiustificativo() {
        return $this->tipologie_giustificativo;
    }

    public function setTipologieGiustificativo($tipologie_giustificativo) {
        $this->tipologie_giustificativo = $tipologie_giustificativo;
    }

    public function getTipologiaGiustificativo($codice) {
        if (!is_null($this->getTipologieGiustificativo())) {
            foreach ($this->getTipologieGiustificativo() as $tipologia) {
                if ($tipologia->getCodice() == $codice) {
                    return $tipologia;
                }
            }
        }

        return null;
    }

    public function getTipologieGiustificativoVisibili() {
        $tipologie = array();
        if (!is_null($this->getTipologieGiustificativo())) {
            foreach ($this->getTipologieGiustificativo() as $tipologia) {
                if (is_null($tipologia->getInvisibile()) || $tipologia->getInvisibile() == false) {
                    $tipologie[] = $tipologia;
                }
            }
        }

        return $tipologie;
    }

    public function removeAtti(Atto $atti): void
    {
        $this->atti->removeElement($atti);
    }

    public function setPrioritaProcedura(?PrioritaProcedura $prioritaProcedura): self
    {
        $this->priorita_procedura = $prioritaProcedura;

        return $this;
    }
    
    public function getDocumentiRichiesti() {
        return $this->documenti_richiesti;
    }

    public function setDocumentiRichiesti($documenti_richiesti) {
        $this->documenti_richiesti = $documenti_richiesti;
        return $this;
    }
    
    public function getCodiceTipoProcedura() {
        if ($this instanceof ManifestazioneInteresse) {
            return "MANIFESTAZIONE_INTERESSE";
        }
        if ($this instanceof Bando) {
            return "BANDO";
        }
        if ($this instanceof AssistenzaTecnica) {
            return "ASSISTENZA_TECNICA";
        }
        if ($this instanceof IngegneriaFinanziaria) {
            return "INGEGNERIA_FINANZIARIA";
        }
        if ($this instanceof Acquisizioni) {
            return "ACQUISIZIONI";
        }
    }

    public function isAssistenzaTecnica() {
        return $this->getCodiceTipoProcedura() == 'ASSISTENZA_TECNICA' ? true : false;
    }

    public function isIngegneriaFinanziaria() {
        return $this->getCodiceTipoProcedura() == 'INGEGNERIA_FINANZIARIA' ? true : false;
    }

    public function isAcquisizioni() {
        return $this->getCodiceTipoProcedura() == 'ACQUISIZIONI' ? true : false;
    }	

    public function isProceduraParticolare() {
        if ($this->isAssistenzaTecnica() || $this->isIngegneriaFinanziaria() || $this->isAcquisizioni()) {
            return true;
        } else {
            return false;
        }
    }

    public function isBandoImportato() {
        $importati = array(7, 8);
        return in_array($this->getId(), $importati);
    }

    public function isModificabile() {
        return false;
    }

    public abstract function getTipo();
    
    public function getRendicontazioneAttiva() {
        return $this->rendicontazioneAttiva;
    }

    public function setRendicontazioneAttiva($rendicontazioneAttiva) {
        $this->rendicontazioneAttiva = $rendicontazioneAttiva;
    }

    /**
    * @return \MonitoraggioBundle\Entity\TC1ProceduraAttivazione
    */
    public function getMonProcAtt() {
        return $this->mon_proc_att;
    }

    public function getMonCodAiutoRna() {
        return $this->mon_cod_aiuto_rna;
    }

    public function getMonFlagAiuti() {
        return $this->mon_flag_aiuti;
    }

    public function setMonProcAtt($mon_proc_att) {
        $this->mon_proc_att = $mon_proc_att;
    }

    public function setMonCodAiutoRna($mon_cod_aiuto_rna) {
        $this->mon_cod_aiuto_rna = $mon_cod_aiuto_rna;
    }

    public function setMonFlagAiuti($mon_flag_aiuti) {
        $this->mon_flag_aiuti = $mon_flag_aiuti;
    }

    /**
     * @return Collection|ProgrammaProcedura[]
     */
    public function getMonProcedureProgrammi(): Collection {
        return $this->mon_procedure_programmi;
    }

    public function setMonProcedureProgrammi(Collection $mon_procedure_programmi): self {
        $this->mon_procedure_programmi = $mon_procedure_programmi;
        
        return $this;
    }

    public function getMonImporto(): ?float{
        return $this->mon_importo;
    }

    public function setMonImporto(?float $value): self{
        $this->mon_importo = $value;

        return $this;
    }

    public function getMonTipoProceduraAttivazione(): ?TC2TipoProceduraAttivazione {
        return $this->mon_tipo_procedura_attivazione;
    }
    
    public function getTestoMailIstruttoria(): string {
        $testoBase = "Buongiorno, in allegato l’elenco delle integrazioni richieste.\n\nIl Responsabile del procedimento ";
        $resp = $this->getResponsabile()->getPersona();
        return $testoBase.$resp->getNome()." ".$resp->getCognome();
    }
    
    public function getRendicontazioneProceduraConfig() {
        return $this->rendicontazioneProceduraConfig;
    }

    public function setRendicontazioneProceduraConfig($rendicontazioneProceduraConfig) {
        $this->rendicontazioneProceduraConfig = $rendicontazioneProceduraConfig;
    }
        
    public function getModalitaPagamentoProceduraByCodice($codice): ?ModalitaPagamentoProcedura {
        $modalita = null;
        foreach ($this->modalita_pagamento as $mp){
            if($mp->getModalitaPagamento()->getCodice() == $codice){
                $modalita = $mp;
            }
        }
        return $modalita;
    }

    /**
    * @param \MonitoraggioBundle\Entity\TC2TipoProceduraAttivazione $value
    */	
    public function setMonTipoProceduraAttivazione(?TC2TipoProceduraAttivazione $value): self{
        $this->mon_tipo_procedura_attivazione  = $value;

        return $this;
    }

    function getMonDataAvvioProcedura(): ?\DateTime {
        return $this->mon_data_avvio_procedura;
    }

    function getMonDataFineProcedura(): ?\DateTime {
        return $this->mon_data_fine_procedura;
    }

    function setMonDataAvvioProcedura(?\DateTime  $mon_data_avvio_procedura):self {
        $this->mon_data_avvio_procedura = $mon_data_avvio_procedura;
        
        return $this;
    }

    function setMonDataFineProcedura(?\DateTime  $mon_data_fine_procedura):self {
        $this->mon_data_fine_procedura = $mon_data_fine_procedura;
        
        return $this;
    }
    
    public function getDataApprovazione(): ?\DateTime
    {
        return $this->data_approvazione;
    }

    public function setDataApprovazione(?\DateTime $data_approvazione): self
    {
        $this->data_approvazione = $data_approvazione;
        
        return $this;
    }

    public function setDataAvvioLavoriPreparatori(?\DateTime  $dataAvvioLavoriPreparatori): self
    {
        $this->data_avvio_lavori_preparatori = $dataAvvioLavoriPreparatori;

        return $this;
    }

    public function getDataAvvioLavoriPreparatori(): ?\DateTime
    {
        return $this->data_avvio_lavori_preparatori;
    }

    public function addTipologieGiustificativo(TipologiaGiustificativo $tipologieGiustificativo): self
    {
        $this->tipologie_giustificativo[] = $tipologieGiustificativo;

        return $this;
    }

    public function removeTipologieGiustificativo(TipologiaGiustificativo $tipologieGiustificativo): void
    {
        $this->tipologie_giustificativo->removeElement($tipologieGiustificativo);
    }

    public function addMonProcedureProgrammi(ProgrammaProcedura $monProcedureProgrammi): self
    {
        $this->mon_procedure_programmi[] = $monProcedureProgrammi;

        return $this;
    }

    public function removeMonProcedureProgrammi(ProgrammaProcedura $monProcedureProgrammi): void
    {
        $this->mon_procedure_programmi->removeElement($monProcedureProgrammi);
    }

    public function addRichieste(Richiesta $richieste): self
    {
        $this->richieste[] = $richieste;

        return $this;
    }

    public function removeRichieste(Richiesta $richieste): void
    {
        $this->richieste->removeElement($richieste);
    }

    /**
     * @return Richiesta[]|Collection 
     */
    public function getRichieste(): Collection
    {
        return $this->richieste;
    }

    public function addSezioniPianiCosto(SezionePianoCosto $sezioniPianiCosto): self
    {
        $this->sezioni_piani_costo[] = $sezioniPianiCosto;

        return $this;
    }

    public function removeSezioniPianiCosto(SezionePianoCosto $sezioniPianiCosto): void
    {
        $this->sezioni_piani_costo->removeElement($sezioniPianiCosto);
    }

    public function addDocumentiRichiesti(TipologiaDocumento $documentiRichiesti): self
    {
        $this->documenti_richiesti[] = $documentiRichiesti;

        return $this;
    }

    public function removeDocumentiRichiesti(TipologiaDocumento $documentiRichiesti): void
    {
        $this->documenti_richiesti->removeElement($documentiRichiesti);
    }

    public function addTipologieQuietanze(TipologiaQuietanza $tipologieQuietanze): self
    {
        $this->tipologie_quietanze[] = $tipologieQuietanze;

        return $this;
    }

    public function removeTipologieQuietanze(TipologiaQuietanza $tipologieQuietanze): void
    {
        $this->tipologie_quietanze->removeElement($tipologieQuietanze);
    }

    public function setDataDelibera(?\DateTime $dataDelibera): self
    {
        $this->data_delibera = $dataDelibera;

        return $this;
    }

    public function getDataDelibera(): ?\DateTime
    {
        return $this->data_delibera;
    }

    public function setMonCodiceProceduraAttivazione(?string $monCodiceProceduraAttivazione): self
    {
        $this->mon_codice_procedura_attivazione = $monCodiceProceduraAttivazione;

        return $this;
    }

    public function getMonCodiceProceduraAttivazione(): ?string
    {
        return $this->mon_codice_procedura_attivazione;
    }
    
    public function getClassificaRend(): ?string {
        return $this->classifica_rend;
    }

    public function getFascicoloPrincipaleRend(): ?string {
        return $this->fascicolo_principale_rend;
    }

    public function getAnnoProtocollazioneRend(): ?string {
        return $this->anno_protocollazione_rend;
    }

    public function getUnitaOrganizzativaRend(): ?string {
        return $this->unita_organizzativa_rend;
    }

    public function getClassificaCtrl(): ?string {
        return $this->classifica_ctrl;
    }

    public function getFascicoloPrincipaleCtrl(): ?string {
        return $this->fascicolo_principale_ctrl;
    }

    public function getAnnoProtocollazioneCtrl(): ?string {
        return $this->anno_protocollazione_ctrl;
    }

    public function getUnitaOrganizzativaCtrl(): ?string {
        return $this->unita_organizzativa_ctrl;
    }

    public function setClassificaRend(?string $classifica_rend): self {
        $this->classifica_rend = $classifica_rend;

        return $this;
    }

    public function setFascicoloPrincipaleRend(?string $fascicolo_principale_rend): self {
        $this->fascicolo_principale_rend = $fascicolo_principale_rend;

        return $this;
    }

    public function setAnnoProtocollazioneRend(?string $anno_protocollazione_rend): self {
        $this->anno_protocollazione_rend = $anno_protocollazione_rend;

        return $this;
    }

    public function setUnitaOrganizzativaRend(?string $unita_organizzativa_rend): self {
        $this->unita_organizzativa_rend = $unita_organizzativa_rend;
        
        return $this;
    }

    public function setClassificaCtrl(?string $classifica_ctrl): self {
        $this->classifica_ctrl = $classifica_ctrl;
        
        return $this;
    }

    public function setFascicoloPrincipaleCtrl(?string $fascicolo_principale_ctrl): self {
        $this->fascicolo_principale_ctrl = $fascicolo_principale_ctrl;
        
        return $this;
    }

    public function setAnnoProtocollazioneCtrl(?string $anno_protocollazione_ctrl): self {
        $this->anno_protocollazione_ctrl = $anno_protocollazione_ctrl;
        
        return $this;
    }

    public function setUnitaOrganizzativaCtrl(?string $unita_organizzativa_ctrl): self {
        $this->unita_organizzativa_ctrl = $unita_organizzativa_ctrl;
        
        return $this;
    }

    public function getAbilitaReinvioNonAmmesse(): ?bool {
        return $this->abilita_reinvio_non_ammesse;
    }

    public function setAbilitaReinvioNonAmmesse(?bool $abilita_reinvio_non_ammesse): self {
        $this->abilita_reinvio_non_ammesse = $abilita_reinvio_non_ammesse;
        
        return $this;
    }
    
    public function getProrogaAttiva(): ?bool {
        return $this->proroga_attiva;
    }

    public function setProrogaAttiva(?bool $proroga_attiva) {
        $this->proroga_attiva = $proroga_attiva;
    }
    
    public function getUtenteRobot(): ?string {
        return $this->utente_robot;
    }

    public function getUtenteRobotRend(): ?string {
        return $this->utente_robot_rend;
    }

    public function getUtenteRobotCtrl(): ?string {
        return $this->utente_robot_ctrl;
    }

    public function setUtenteRobot(?string $utente_robot):self {
        $this->utente_robot = $utente_robot;

        return $this;
    }

    public function setUtenteRobotRend(?string $utente_robot_rend):self {
        $this->utente_robot_rend = $utente_robot_rend;

        return $this;
    }

    public function setUtenteRobotCtrl(?string $utente_robot_ctrl):self {
        $this->utente_robot_ctrl = $utente_robot_ctrl;

        return $this;
    }

    public function getIdUteInRobot() {
        return $this->IdUteInRobot;
    }

    public function getIdUoInRobot() {
        return $this->idUoInRobot;
    }

    public function getIdUteInRendRobot() {
        return $this->IdUteInRendRobot;
    }

    public function getIdUoInRendRobot() {
        return $this->IdUoInRendRobot;
    }

    public function getIdUteInCtrlRobot() {
        return $this->IdUteInCtrlRobot;
    }

    public function getIdUoInCtrlRobot() {
        return $this->IdUoInCtrlRobot;
    }

    public function setIdUteInRobot($IdUteInRobot):self {
        $this->IdUteInRobot = $IdUteInRobot;

        return $this;
    }

    public function setIdUoInRobot($idUoInRobot):self {
        $this->idUoInRobot = $idUoInRobot;

        return $this;
    }

    public function setIdUteInRendRobot($IdUteInRendRobot):self {
        $this->IdUteInRendRobot = $IdUteInRendRobot;

        return $this;
    }

    public function setIdUoInRendRobot($IdUoInRendRobot):self {
        $this->IdUoInRendRobot = $IdUoInRendRobot;

        return $this;
    }

    public function setIdUteInCtrlRobot($IdUteInCtrlRobot):self {
        $this->IdUteInCtrlRobot = $IdUteInCtrlRobot;

        return $this;
    }

    public function setIdUoInCtrlRobot($IdUoInCtrlRobot):self {
        $this->IdUoInCtrlRobot = $IdUoInCtrlRobot;

        return $this;
    }

    public function getControlli() {
        return $this->controlli;
    }

    public function setControlli($controlli):self {
        $this->controlli = $controlli;

        return $this;
    }

    public function hasPianoCostiMultiSezione():bool {
        return count($this->sezioni_piani_costo) > 1;
    }
    
    public function getSedeMontana() {
        return $this->sede_montana;
    }

    public function setSedeMontana($sede_montana):self {
        $this->sede_montana = $sede_montana;

        return $this;
    }
    
    public function getRisorseProgetto() {
        return $this->risorse_progetto;
    }

    public function setRisorseProgetto($risorse_progetto):self {
        $this->risorse_progetto = $risorse_progetto;

        return $this;
    }
    
    public function hasRisorseProgetto() {
        return !is_null($this->risorse_progetto) && $this->risorse_progetto == true;	
    }

    public function setDataInoltroPagamento(?\DateTime $dataInoltroPagamento): self {
        $this->data_inoltro_pagamento = $dataInoltroPagamento;

        return $this;
    }

    public function getDataInoltroPagamento():?\DateTime
    {
        return $this->data_inoltro_pagamento;
    }

    public function addControlli(ControlloProcedura $controlli):self {
        $this->controlli[] = $controlli;

        return $this;
    }

    public function removeControlli(ControlloProcedura $controlli):void {
        $this->controlli->removeElement($controlli);
    }
    
    public function getComportamentoParticolareVariazione() {
        return $this->comportamento_particolare_variazione;
    }

    public function setComportamentoParticolareVariazione($comportamento_particolare_variazione) {
        $this->comportamento_particolare_variazione = $comportamento_particolare_variazione;
    }

    public function isComportamentoParticolareVariazione() {
        return $this->comportamento_particolare_variazione == true;
    }

    public function getTipiAiutoProponente() {
        return $this->tipi_aiuto_proponente;
    }

    public function setTipiAiutoProponente($tipi_aiuto_proponente) {
        $this->tipi_aiuto_proponente = $tipi_aiuto_proponente;
    }

    public function getAmmettePivaCfDuplicati()
    {
        return $this->ammette_piva_cf_duplicati;
    }

    public function setAmmettePivaCfDuplicati(?bool $ammettePivaCfDuplicati): self
    {
        $this->ammette_piva_cf_duplicati = $ammettePivaCfDuplicati;

        return $this;
    }

    public function isAmmettePivaDuplicati(): bool {
        return $this->ammette_piva_cf_duplicati == true;
    }
    
    public function setNumeroMassimoRichiesteProcedura(?int $numeroMassimoRichiesteProcedura): self
    {
        $this->numero_massimo_richieste_procedura = $numeroMassimoRichiesteProcedura;

        return $this;
    }

    public function getNumeroMassimoRichiesteProcedura(): ?int
    {
        return $this->numero_massimo_richieste_procedura;
    }
    
    /**
     * @return TC44_45IndicatoriOutput[]
     */
    public function getIndicatoriAssociati(\DateTimeInterface $ref = null): array {        
        /** @var TC44_45IndicatoriOutput[] $indicatori */
        $indicatori = [];		
        /** @var Azione $azione */
        foreach($this->azioni as $azione){
            $indicatori = \array_merge($indicatori,
                $azione->getIndicatoriOutputAzioni($ref)->filter(function(IndicatoriOutputAzioni $i){
                    return $i->getAsse() == $this->asse;
                })
                ->map(function(IndicatoriOutputAzioni $i){
                    return $i->getIndicatoreOutput();
                })->toArray()
            );
        }
        $indicatoriUnici = \array_unique($indicatori);

        return $indicatoriUnici;
    }
    
    /**
     * @return Collection|\MonitoraggioBundle\Entity\TC36LivelloGerarchico[]
     */
    public function getLivelliGerarchici(): Collection {
        $livelli = \array_map(function(Azione $azione){
            return $azione->getObiettivoSpecifico()->getLivelloGerarchico();
        },$this->azioni->toArray());
        $livelli = \array_filter($livelli, function($value){
            return ! \is_null($value);
        });
        
        return new ArrayCollection(\array_unique($livelli));
    }
    
    public function getGeneratoreEntrate() {
        return $this->generatore_entrate;
    }

    public function setGeneratoreEntrate($generatore_entrate) {
        $this->generatore_entrate = $generatore_entrate;
    }
    
    public function isGeneratoreEntrate() {
        return $this->generatore_entrate == true;
    }

    public function getIndicatoriRisultato(): array
    {
        $associazioniIndicatori = $this->azioni->map(function(Azione $azione): Collection {
            return $azione->getObiettivoSpecifico()->getAssociazioniIndicatoriRisultato();
        })->toArray();
        $indicatoriRisultatoConDuplicati = \array_reduce($associazioniIndicatori,function(array $carry, Collection $collectionAssociazioni): array {
            $indicatori = $collectionAssociazioni->map(function(IndicatoriRisultatoObiettivoSpecifico $associazione): TC42_43IndicatoriRisultato{
                return $associazione->getIndicatoreRisultato();
            })->toArray();
            
            return \array_merge($carry, $indicatori);
        }, []);
        $indicatoriRisultato = \array_unique($indicatoriRisultatoConDuplicati);
        return $indicatoriRisultato;
    }
    
    public function getRegistroProtocollazione() {
        return $this->registro_protocollazione;
    }

    public function getServizioProtocollazione() {
        return $this->servizio_protocollazione;
    }

    public function setRegistroProtocollazione($registro_protocollazione) {
        $this->registro_protocollazione = $registro_protocollazione;
    }

    public function setServizioProtocollazione($servizio_protocollazione) {
        $this->servizio_protocollazione = $servizio_protocollazione;
    }


    public function addTipoAiuto(TipoAiuto $tipoAiuto): self {
        $this->tipo_aiuto[] = $tipoAiuto;

        return $this;
    }

    public function removeTipoAiuto(TipoAiuto $tipoAiuto): void {
        $this->tipo_aiuto->removeElement($tipoAiuto);
    }

    public function setMonTipoBeneficiario(string $monTipoBeneficiario): self {
        $this->mon_tipo_beneficiario = $monTipoBeneficiario;

        return $this;
    }

    public function getMonTipoBeneficiario(): string {
        return $this->mon_tipo_beneficiario;
    }

    /**
     * @return int|null
     */
    public function getAttualeFinestraTemporalePresentazione(): ?int {
        return $this->attuale_finestra_temporale_presentazione;
    }

    /**
     * @param int|null $attuale_finestra_temporale_presentazione
     */
    public function setAttualeFinestraTemporalePresentazione(?int $attuale_finestra_temporale_presentazione): void {
        $this->attuale_finestra_temporale_presentazione = $attuale_finestra_temporale_presentazione;
    }

    /**
     * @return bool
     */
    public function isRichiestaFirmaDigitale(): bool
    {
        return $this->richiesta_firma_digitale;
    }

    /**
     * @param bool $richiesta_firma_digitale
     */
    public function setRichiestaFirmaDigitale(bool $richiesta_firma_digitale): void
    {
        $this->richiesta_firma_digitale = $richiesta_firma_digitale;
    }

    /**
     * @return bool
     */
    public function isRichiestaFirmaDigitaleStepSuccessivi(): bool
    {
        return $this->richiesta_firma_digitale_step_successivi;
    }

    /**
     * @param bool $richiesta_firma_digitale_step_successivi
     */
    public function setRichiestaFirmaDigitaleStepSuccessivi(bool $richiesta_firma_digitale_step_successivi): void
    {
        $this->richiesta_firma_digitale_step_successivi = $richiesta_firma_digitale_step_successivi;
    }

    /**
     * @return bool
     */
    public function isSezioneIstruttoriaCup(): bool
    {
        return $this->sezione_istruttoria_cup;
    }

    /**
     * @param bool $sezione_istruttoria_cup
     */
    public function setSezioneIstruttoriaCup(bool $sezione_istruttoria_cup): void
    {
        $this->sezione_istruttoria_cup = $sezione_istruttoria_cup;
    }

    /**
     * @return bool
     */
    public function isSezioneIstruttoriaNucleo(): bool
    {
        return $this->sezione_istruttoria_nucleo;
    }

    /**
     * @param bool $sezione_istruttoria_nucleo
     */
    public function setSezioneIstruttoriaNucleo(bool $sezione_istruttoria_nucleo): void
    {
        $this->sezione_istruttoria_nucleo = $sezione_istruttoria_nucleo;
    }
    
    public function isMultiPianoCosto(): bool {
        return $this->multi_piano_costo == true;
    }
    
    public function getSpeseAmmissibiliForfettario() {
        return $this->spese_ammissibili_forfettario;
    }

    public function getSpesePubblicheForfettario() {
        return $this->spese_pubbliche_forfettario;
    }

    public function setSpeseAmmissibiliForfettario($spese_ammissibili_forfettario) {
        $this->spese_ammissibili_forfettario = $spese_ammissibili_forfettario;
    }

    public function setSpesePubblicheForfettario($spese_pubbliche_forfettario) {
        $this->spese_pubbliche_forfettario = $spese_pubbliche_forfettario;
    }

    /**
     * @return bool
     */
    public function isMostraContatoreRichiestePresentate(): bool
    {
        return $this->mostra_contatore_richieste_presentate;
    }

    /**
     * @param bool $mostra_contatore_richieste_presentate
     */
    public function setMostraContatoreRichiestePresentate(bool $mostra_contatore_richieste_presentate): void
    {
        $this->mostra_contatore_richieste_presentate = $mostra_contatore_richieste_presentate;
    }
    
    /**
     * @return $string
     */
    public function getAzioniString() {
        $string = '';
        foreach ($this->azioni as $azione) {
            $string = $string . $azione . ' ';
        }
        return $string;
    }
    
    /**
     * @return $string
     */
    public function getObiettiviSpcString() {
        $string = '';
        foreach ($this->azioni as $azione) {
           $string = $string . $azione->getObiettivoSpecifico() . ' ';
        }
        return $string;
    }
    
    /**
     * @return $string
     */
    public function getTipiAiutoString() {
        $string = '';
        foreach ($this->tipo_aiuto as $aiuto) {
           $string = $string . $aiuto->getDescrizione() . ' ';
        }
        return $string;
    }

    /**
     * @return string|null
     */
    public function getCentroDiCosto(): ?string
    {
        return $this->centro_di_costo;
    }

    /**
     * @param string|null $centro_di_costo
     */
    public function setCentroDiCosto(?string $centro_di_costo): void
    {
        $this->centro_di_costo = $centro_di_costo;
    }

    /**
     * @return bool
     */
    public function isBandoPaceOCittadinanza(): bool
    {
        // Controllo la classifica in modo tale da non dover aggiungere per ogni nuovo bando un ID.
        if ($this->getClassifica() == "100.110.100") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isBandoCentriStoriciColpitiDalSisma(): bool
    {
        $bandiCentriStoriciSisma = $this->getIdBandiCentriStoriciColpitiDalSisma(false);
        if (in_array($this->getId(), $bandiCentriStoriciSisma)) {
            return true;
        }
        return false;
    }

    /**
     * @param bool $returnString
     * @return int[]|string
     */
    public function getIdBandiCentriStoriciColpitiDalSisma(bool $returnString = true)
    {
        $bandiCentriStoriciSisma = [95, 121, 132, 167];
        if ($returnString) {
            return implode(',', $bandiCentriStoriciSisma);
        } else {
            return $bandiCentriStoriciSisma;
        }
    }

    /**
     * @return bool
     */
    public function isBandoIrap(): bool
    {
        $bandiIrap = [118, 125];
        if (in_array($this->getId(), $bandiIrap)) {
            return true;
        }
        return false;
    }
    
    public function getDnshProcedura() {
        return $this->dnsh_procedura;
    }

    public function setDnshProcedura($dnsh_procedura): void {
        $this->dnsh_procedura = $dnsh_procedura;
    }
    
    public function getRichiestaFirmaDigitale() {
        return $this->richiesta_firma_digitale;
    }

    public function isNuovaProgrammazione(): bool
    {
        return in_array($this->id, [176,177,178,179,181,183,185,190]);
    }

    /**
     * @return mixed
     */
    public function getSezioneAmbitiTematiciS3()
    {
        return $this->sezione_ambiti_tematici_s3;
    }

    /**
     * @param mixed $sezione_ambiti_tematici_s3
     */
    public function setSezioneAmbitiTematiciS3($sezione_ambiti_tematici_s3): void
    {
        $this->sezione_ambiti_tematici_s3 = $sezione_ambiti_tematici_s3;
    }

    /**
     * @return mixed
     */
    public function getAmbitiTematiciS3Multipli()
    {
        return $this->ambiti_tematici_s3_multipli;
    }

    /**
     * @param mixed $ambiti_tematici_s3_multipli
     */
    public function setAmbitiTematiciS3Multipli($ambiti_tematici_s3_multipli): void
    {
        $this->ambiti_tematici_s3_multipli = $ambiti_tematici_s3_multipli;
    }

    /**
     * @return mixed
     */
    public function getAmbitiTematiciS3DescrizioneDescrittori()
    {
        return $this->ambiti_tematici_s3_descrizione_descrittori;
    }

    /**
     * @param mixed $ambiti_tematici_s3_descrizione_descrittori
     */
    public function setAmbitiTematiciS3DescrizioneDescrittori($ambiti_tematici_s3_descrizione_descrittori): void
    {
        $this->ambiti_tematici_s3_descrizione_descrittori = $ambiti_tematici_s3_descrizione_descrittori;
    }

    /**
     * @return mixed
     */
    public function getRup()
    {
        return $this->rup;
    }

    /**
     * @param mixed $rup
     */
    public function setRup($rup): void
    {
        $this->rup = $rup;
    }

    /**
     * @return string
     */
    public function getNomeCognomeRup(): string
    {
        if ($this->getRup() && $this->getRup()->getPersona()) {
            return $this->getRup()->getPersona()->getNome()
                . ' ' . $this->getRup()->getPersona()->getCognome();
        }

        return '';
    }
}
