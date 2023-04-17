<?php

namespace SoggettoBundle\Entity;

use AnagraficheBundle\Entity\Persona;
use AttuazioneControlloBundle\Entity\Partita;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use BaseBundle\Entity\Indirizzo;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use GeoBundle\Entity\GeoComune;
use GeoBundle\Entity\GeoStato;
use RichiesteBundle\Entity\Proponente;

/**
 * @ORM\Entity(repositoryClass="SoggettoBundle\Entity\SoggettoRepository")
 * @ORM\Table(name="soggetti",
 *  indexes={
 *      @ORM\Index(name="idx_forma_giuridica_id", columns={"forma_giuridica_id"}),
 *  })
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({"SOGGETTO" = "SoggettoBundle\Entity\Soggetto",
 *                          "AZIENDA"  = "SoggettoBundle\Entity\Azienda",
 *                          "COMUNE"   = "SoggettoBundle\Entity\ComuneUnione",
 *                          "OOII"     = "SoggettoBundle\Entity\OrganismoIntermedio",
 *                          "PERSONA_FISICA" = "SoggettoBundle\Entity\PersonaFisica"
 * })
 *
 * @Assert\Callback(callback="checkSelezioneStato")
 */
class Soggetto extends EntityLoggabileCancellabile {

    const ID_REGIONE = 3438;
    const AZIENDA = 'AZIENDA';
    const PROFESSIONISTA = 'LIBERI_PROFESSIONISTI';
    const COMUNE = 'COMUNE';
    const UNIVERSITA = 'UNIVERSITA';
    const PERSONA_FISICA = 'PERSONA_FISICA';
    const ALTRI = 'SOGGETTO';
    const TESTO_AZIENDA = 'Azienda, impresa, società, studio associato e STP';
    const TESTO_PROFESSIONISTA = 'Imprenditore individuale, libero professionista e lavoratore autonomo';
    const TESTO_COMUNE = 'Comune/Unione di Comuni';
    const TESTO_UNIVERSITA = 'Università/Laboratorio di Ricerca';
    const TESTO_PERSONA_FISICA = 'Persona fisica';
    const TESTO_ALTRI = 'Soggetto Giuridico';

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=150)
     */
    private $denominazione;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Assert\Length(min = "11", max = "11", groups={"statoItalia"})
     */
    private $partita_iva;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $senza_piva;

    /**
     * @ORM\Column(type="string", length=16, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=16, groups={"statoItalia"})
     */
    private $codice_fiscale;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $data_registrazione;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $data_costituzione;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $sito_web;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Regex(pattern="/^\d+$/",match=true)
     */
    private $dimensione;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     * @Assert\NotBlank()
     */
    private $tel;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $fax;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $via;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\NotBlank()
     */
    private $civico;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Length(min = "5", max = "5", groups={"statoItalia"})
     */
    private $cap;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string|null
     */
    private $localita;

    /**
     * @ORM\OneToMany(targetEntity="SoggettoBundle\Entity\Sede", mappedBy="soggetto", cascade={"remove"})
     * @var Collection|Sede[]
     */
    private $sedi;

    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Ateco")
     * @ORM\JoinColumn(name="codice_ateco_id", referencedColumnName="id")
     * @var Ateco|null
     */
    private $codice_ateco;

    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Ateco")
     * @ORM\JoinColumn(name="codice_ateco_secondario_id", referencedColumnName="id", nullable=true))
     * @var Ateco|null
     */
    private $codice_ateco_secondario;

    /**
     * @var GeoStato $stato
     *
     * @ORM\ManyToOne(targetEntity="GeoBundle\Entity\GeoStato", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotNull(message="Devi selezionare lo stato")
     * 
     * @var GeoStato|null
     */
    protected $stato;

    /**
     * @ORM\ManyToOne(targetEntity="GeoBundle\Entity\GeoComune")
     * @ORM\JoinColumn(name="comune_id", referencedColumnName="id")
     * 
     * @var GeoComune|null
     */
    private $comune;

    /**
     * @var string $provinciaEstera
     *
     * @ORM\Column(name="provinciaEstera", type="string", length=255, nullable=true)
     */
    protected $provinciaEstera;

    /**
     * @var string $comuneEstero
     *
     * @ORM\Column(name="comuneEstero", type="string", length=255, nullable=true)
     */
    protected $comuneEstero;

    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\FormaGiuridica", inversedBy="soggetto")
     * @ORM\JoinColumn(name="forma_giuridica_id", referencedColumnName="id")
     *
     * @Assert\NotBlank()
     * @var FormaGiuridica
     */
    private $forma_giuridica;

    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\TipoSoggetto")
     * @ORM\JoinColumn(name="tipo_soggetto_id", referencedColumnName="id")
     */
    private $tipo_soggetto;

    /**
     *
     * @ORM\OneToMany(targetEntity="IncaricoPersona", mappedBy="soggetto", cascade={"remove"})
     * @var Collection|IncaricoPersona[]
     */
    protected $incarichi_persone;

    /**
     * @ORM\Column(type="bigint", nullable=false)
     */
    private $codice_organismo;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\Proponente", mappedBy="soggetto")
     * @var Collection|Proponente[]
     */
    private $proponenti;
    protected $disabilita_combo;
    //proprietà transient usata per individuare a quale contesto appartiene l'oggetto soggetto salvato in sessione
    protected $contesto;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Email()
     * @Assert\NotBlank(groups={"impresa", "Default"})
     */
    private $email_pec;

    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\DimensioneImpresa")
     * @ORM\JoinColumn()
     */
    private $dimensione_impresa;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $iscritta_inps;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $matricola_inps;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $impresa_iscritta_inps;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $motivazioni_non_iscrizione_inps;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $impresa_iscritta_inail;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $impresa_iscritta_inail_di;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $numero_codice_ditta_impresa_assicurata;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $motivazioni_non_iscrizione_inail;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $ccnl;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $id_sfinge_2013;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $acronimo_laboratorio;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $kp_azienda;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $laboratorio_ricerca;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $lifnr_sap;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $lifnr_sap_created;

    /**
     * @var Collection|Partita[]
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Partita", mappedBy="soggetto")
     */
    protected $partite;


    function getId() {
        return $this->id;
    }

    function getDenominazione() {
        return $this->denominazione;
    }

    function getPartitaIva() {
        return $this->partita_iva;
    }

    function getCodiceFiscale() {
        return strtoupper($this->codice_fiscale);
    }

    function getDataRegistrazione() {
        return $this->data_registrazione;
    }

    /**
     * @return \DateTime|null
     */
    function getDataCostituzione() {
        return $this->data_costituzione;
    }

    function getSitoWeb() {
        return $this->sito_web;
    }

    function getDimensione() {
        return $this->dimensione;
    }

    function getEmail() {
        return $this->email;
    }

    function getTel() {
        return $this->tel;
    }

    function getFax() {
        return $this->fax;
    }

    function getVia() {
        return $this->via;
    }

    function getCivico() {
        return $this->civico;
    }

    function getCap() {
        return $this->cap;
    }

    function getLocalita() {
        return $this->localita;
    }

    function getSedi() {
        return $this->sedi;
    }

    function getCodiceAteco(): ?Ateco {
        return $this->codice_ateco;
    }

    function getComune(): ?GeoComune {
        return $this->comune;
    }

    public function getProvincia() {
        return $this->getComune() ? $this->getComune()->getProvincia() : null;
    }

    /**
     * @return FormaGiuridica
     */
    function getFormaGiuridica() {
        return $this->forma_giuridica;
    }

    function getTipoSoggetto() {
        return $this->tipo_soggetto;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setDenominazione($denominazione) {
        $this->denominazione = $denominazione;
    }

    function setPartitaIva($partita_iva) {
        $this->partita_iva = $partita_iva;
    }

    function setCodiceFiscale($codice_fiscale) {
        $this->codice_fiscale = $codice_fiscale;
    }

    function setDataRegistrazione($data_registrazione) {
        $this->data_registrazione = $data_registrazione;
    }

    function setDataCostituzione($data_costituzione) {
        $this->data_costituzione = $data_costituzione;
    }

    function setSitoWeb($sito_web) {
        $this->sito_web = $sito_web;
    }

    function setDimensione($dimensione) {
        $this->dimensione = $dimensione;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setTel($tel) {
        $this->tel = $tel;
    }

    function setFax($fax) {
        $this->fax = $fax;
    }

    function setVia($via) {
        $this->via = $via;
    }

    function setCivico($civico) {
        $this->civico = $civico;
    }

    function setCap($cap) {
        $this->cap = $cap;
    }

    function setLocalita($localita) {
        $this->localita = $localita;
    }

    function setSedi($sedi) {
        $this->sedi = $sedi;
    }

    function setCodiceAteco($codice_ateco) {
        $this->codice_ateco = $codice_ateco;
    }

    function setComune($comune) {
        $this->comune = $comune;
    }

    public function setProvincia($provincia) {
        
    }

    /**
     * @param FormaGiuridica
     */
    function setFormaGiuridica($forma_giuridica) {
        $this->forma_giuridica = $forma_giuridica;
    }

    function setTipoSoggetto($tipo_soggetto) {
        $this->tipo_soggetto = $tipo_soggetto;
    }

    public function addSede($sede) {
        $this->sedi[] = $sede;
        $sede->setSoggetto($this);
    }

    function getDisabilitaCombo() {
        return $this->disabilita_combo;
    }

    function setDisabilitaCombo($disabilita_combo) {
        $this->disabilita_combo = $disabilita_combo;
    }

    /**
     * @return Collection|IncaricoPersona[]
     */
    public function getIncarichiPersone(): Collection {
        return $this->incarichi_persone;
    }

    public function setIncarichiPersone(Collection $incarichi_persone): self {
        $this->incarichi_persone = $incarichi_persone;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContesto() {
        return $this->contesto;
    }

    /**
     * @param mixed $contesto
     */
    public function setContesto($contesto) {
        $this->contesto = $contesto;
    }

    function getCodiceOrganismo() {
        return $this->codice_organismo;
    }

    function setCodiceOrganismo($codice_organismo) {
        $this->codice_organismo = $codice_organismo;
    }

    public function __construct() {
        $this->sedi = new ArrayCollection();
        $this->incarichi_persone = new ArrayCollection();
        $this->proponenti = new ArrayCollection();
    }

    function getProponenti() {
        return $this->proponenti;
    }

    function setProponenti($proponenti) {
        $this->proponenti = $proponenti;
    }

    public function getSenzaPiva() {
        return $this->senza_piva;
    }

    public function setSenzaPiva($senza_piva): void {
        $this->senza_piva = $senza_piva;
    }

    public function isSenzaPiva() {
        return $this->senza_piva == true;
    }

    public function __toString() {
        return $this->denominazione;
    }

    public function getLr(): ?Persona {
        $legaleRappresentante = $this->incarichi_persone->filter(function (IncaricoPersona $incarico): bool {
                    $isLegaleRappresentante = TipoIncarico::LR == $incarico->getTipoIncarico()->getCodice();

                    return $incarico->isAttivo() && $isLegaleRappresentante;
                })
                ->map(function (IncaricoPersona $incarico): ?Persona {
                    return $incarico->getIncaricato();
                })
                ->last();

        return $legaleRappresentante ?: null;
    }

    /**
     * @Assert\Callback
     */
    public function valida(ExecutionContextInterface $context) {
        if ($this instanceof Azienda) {
            $isProfessionista = !is_null($this->forma_giuridica) && $this->forma_giuridica->isProfessionista(); // Libero professionista
            $isAsdSsd = !is_null($this->forma_giuridica) && $this->forma_giuridica->isAsdSsd(); // ASD e SSD

            if (is_null($this->getPartitaIva()) && $this->hasObbligoPartitaIva()) {
                $context->buildViolation('Questo valore non dovrebbe essere vuoto.')->atPath('partita_iva')->addViolation();
            }

            if (is_null($this->getDataCostituzione()) && !$isProfessionista) {
                $context->buildViolation('Questo valore non dovrebbe essere vuoto.')->atPath('data_costituzione')->addViolation();
            }

            if (!is_null($this->forma_giuridica) && is_null($this->dimensione_impresa) && (!$isProfessionista && !$isAsdSsd)) {
                $context->buildViolation('Questo valore non dovrebbe essere vuoto.')->atPath('dimensione_impresa')->addViolation();
            }
        }
    }

    /**
     * @return mixed
     */
    public function getEmailPec() {
        return $this->email_pec;
    }

    /**
     * @param mixed $email_pec
     */
    public function setEmailPec($email_pec) {
        $this->email_pec = $email_pec;
    }

    function getDimensioneImpresa() {
        return $this->dimensione_impresa;
    }

    function setDimensioneImpresa($dimensione_impresa) {
        $this->dimensione_impresa = $dimensione_impresa;
    }

    public function getSoggetto() {
        return $this;
    }

    function getMatricolaInps() {
        return $this->matricola_inps;
    }

    function getImpresaIscrittaInps() {
        return $this->impresa_iscritta_inps;
    }

    function getMotivazioniNonIscrizioneInps() {
        return $this->motivazioni_non_iscrizione_inps;
    }

    function getImpresaIscrittaInail() {
        return $this->impresa_iscritta_inail;
    }

    function getImpresaIscrittaInailDi() {
        return $this->impresa_iscritta_inail_di;
    }

    function getNumeroCodiceDittaImpresaAssicurata() {
        return $this->numero_codice_ditta_impresa_assicurata;
    }

    function getMotivazioniNonIscrizioneInail() {
        return $this->motivazioni_non_iscrizione_inail;
    }

    function getCcnl() {
        return $this->ccnl;
    }

    function setMatricolaInps($matricola_inps) {
        $this->matricola_inps = $matricola_inps;
    }

    function setImpresaIscrittaInps($impresa_iscritta_inps) {
        $this->impresa_iscritta_inps = $impresa_iscritta_inps;
    }

    function setMotivazioniNonIscrizioneInps($motivazioni_non_iscrizione_inps) {
        $this->motivazioni_non_iscrizione_inps = $motivazioni_non_iscrizione_inps;
    }

    function setImpresaIscrittaInail($impresa_iscritta_inail) {
        $this->impresa_iscritta_inail = $impresa_iscritta_inail;
    }

    function setImpresaIscrittaInailDi($impresa_iscritta_inail_di) {
        $this->impresa_iscritta_inail_di = $impresa_iscritta_inail_di;
    }

    function setNumeroCodiceDittaImpresaAssicurata($numero_codice_ditta_impresa_assicurata) {
        $this->numero_codice_ditta_impresa_assicurata = $numero_codice_ditta_impresa_assicurata;
    }

    function setMotivazioniNonIscrizioneInail($motivazioni_non_iscrizione_inail) {
        $this->motivazioni_non_iscrizione_inail = $motivazioni_non_iscrizione_inail;
    }

    function setCcnl($ccnl) {
        $this->ccnl = $ccnl;
    }

    function getStato(): ?GeoStato {
        return $this->stato;
    }

    function getProvinciaEstera() {
        return $this->provinciaEstera;
    }

    function getComuneEstero() {
        return $this->comuneEstero;
    }

    function setStato(GeoStato $stato) {
        $this->stato = $stato;
    }

    function setProvinciaEstera($provinciaEstera) {
        $this->provinciaEstera = $provinciaEstera;
    }

    function setComuneEstero($comuneEstero) {
        $this->comuneEstero = $comuneEstero;
    }

    /**
     * validazione in base allo stato
     */
    public function checkSelezioneStato(ExecutionContextInterface $context) {
        if ($this->getStato()) {
            if ($this->getStato()->getDenominazione() == "Italia") {
                if (is_null($this->getProvincia())) {
                    $context->buildViolation('Devi selezionare provincia e comune se lo stato è Italia')->atPath('provincia')->addViolation();
                }
                if (is_null($this->getComune())) {
                    $context->buildViolation('Devi selezionare provincia e comune se lo stato è Italia')->atPath('comune')->addViolation();
                }
                if (!\preg_match("/\d{5}/", $this->getCap())) {
                    $context->buildViolation('Il cap deve essere costituito da cinque cifre se lo stato è Italia')->atPath('cap')->addViolation();
                }
            } else {
                if (is_null($this->getComuneEstero())) {
                    $context->buildViolation('Devi indicare almeno la città se lo stato è diverso da Italia')->atPath('comuneEstero')->addViolation();
                }
            }
        }
    }

    /**
     * Add sedi
     *
     * @param \SoggettoBundle\Entity\Sede $sedi
     *
     * @return Soggetto
     */
    public function addSedi(\SoggettoBundle\Entity\Sede $sedi) {
        $this->sedi[] = $sedi;

        return $this;
    }

    /**
     * Remove sedi
     *
     * @param \SoggettoBundle\Entity\Sede $sedi
     */
    public function removeSedi(\SoggettoBundle\Entity\Sede $sedi) {
        $this->sedi->removeElement($sedi);
    }

    /**
     * Add incarichi_persone
     *
     * @param \SoggettoBundle\Entity\IncaricoPersona $incarichiPersone
     *
     * @return Soggetto
     */
    public function addIncarichiPersone(\SoggettoBundle\Entity\IncaricoPersona $incarichiPersone) {
        $this->incarichi_persone[] = $incarichiPersone;

        return $this;
    }

    /**
     * Remove incarichi_persone
     *
     * @param \SoggettoBundle\Entity\IncaricoPersona $incarichiPersone
     */
    public function removeIncarichiPersone(\SoggettoBundle\Entity\IncaricoPersona $incarichiPersone) {
        $this->incarichi_persone->removeElement($incarichiPersone);
    }

    /**
     * Add proponenti
     *
     * @param \RichiesteBundle\Entity\Proponente $proponenti
     *
     * @return Soggetto
     */
    public function addProponenti(\RichiesteBundle\Entity\Proponente $proponenti) {
        $this->proponenti[] = $proponenti;

        return $this;
    }

    /**
     * Remove proponenti
     *
     * @param \RichiesteBundle\Entity\Proponente $proponenti
     */
    public function removeProponenti(\RichiesteBundle\Entity\Proponente $proponenti) {
        $this->proponenti->removeElement($proponenti);
    }

    /**
     * Set id_sfinge_2013
     *
     * @param string $idSfinge2013
     *
     * @return Soggetto
     */
    public function setIdSfinge2013($idSfinge2013) {
        $this->id_sfinge_2013 = $idSfinge2013;

        return $this;
    }

    /**
     * Get id_sfinge_2013
     *
     * @return string
     */
    public function getIdSfinge2013() {
        return $this->id_sfinge_2013;
    }

    public function setAcronimoLaboratorio($acronimo_laboratorio) {
        $this->acronimo_laboratorio = $acronimo_laboratorio;
    }

    public function setKPAzienda($kp_azienda) {
        $this->kp_azienda = $kp_azienda;
    }

    public function getAcronimoLaboratorio() {
        return $this->acronimo_laboratorio;
    }

    public function getKPAzienda() {
        return $this->kp_azienda;
    }

    protected function hasObbligoPartitaIva() {
        if($this->isSenzaPiva() == true) {
            return false;
        } else {
            return is_null($this->forma_giuridica) || $this->forma_giuridica->hasObbligoPartitaIva();
        }
    }

    public function getTipo() {
        return "SOGGETTO";
    }

    public function isEmiliaRomagna() {
        $comune = $this->getComune();
        if (!is_null($comune)) {
            if ($comune->getProvincia()->getRegione()->getId() == 8) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isSoggettoPubblico() {
        return $this->forma_giuridica->getSoggettoPubblico();
    }

    public function getCodiceAtecoSecondario() {
        return $this->codice_ateco_secondario;
    }

    public function setCodiceAtecoSecondario($codice_ateco_secondario) {
        $this->codice_ateco_secondario = $codice_ateco_secondario;
    }

    public function getIscrittaInps() {
        return $this->iscritta_inps;
    }

    public function setIscrittaInps($iscritta_inps) {
        $this->iscritta_inps = $iscritta_inps;
    }

    public function getLaboratorioRicerca() {
        return $this->laboratorio_ricerca;
    }

    public function setLaboratorioRicerca($laboratorio_ricerca) {
        $this->laboratorio_ricerca = $laboratorio_ricerca;
    }

    /**
     * @return bool
     */
    public function isLaboratorioRicerca(): bool
    {
        return $this->laboratorio_ricerca == true || in_array(is_null($this->forma_giuridica) ? '' : $this->forma_giuridica->getCodice(), FormaGiuridicaRepository::formeGiuridicheUniversita, true);
    }

    /**
     * @return bool
     */
    public function isLiberoProfessionista(): bool
    {
        return in_array(is_null($this->forma_giuridica) ? '' : $this->forma_giuridica->getCodice(), FormaGiuridicaRepository::formeGiuridicheProfessionisti, true);
    }

    /**
     * @return bool
     */
    public function isLiberoProfessionistaSenzaAgricoliEAutonomi(): bool
    {
        return in_array(is_null($this->forma_giuridica) ? '' : $this->forma_giuridica->getCodice(), FormaGiuridicaRepository::formeGiuridicheProfessionistiSenzaAgricoliEAutonomi, true);
    }

    /**
     * @return bool
     */
    public function isComuneUnione(): bool
    {
        return $this->getTipo() == self::COMUNE || in_array(is_null($this->forma_giuridica) ? '' : $this->forma_giuridica->getCodice(), FormaGiuridicaRepository::formeGiuridicheComuniUnioni, true);
    }

    /**
     * @return bool
     */
    public function isAzienda(): bool
    {
        return $this->getTipo() == self::AZIENDA || in_array(is_null($this->forma_giuridica) ? '' : $this->forma_giuridica->getCodice(), FormaGiuridicaRepository::formeGiuridicheAzienda, true);
    }

    /**
     * @return bool
     */
    public function isAltro(): bool
    {
        return $this->getTipo() == self::ALTRI || !in_array(is_null($this->forma_giuridica) ? '1.2.10' : $this->forma_giuridica->getCodice(), array_merge(FormaGiuridicaRepository::formeGiuridicheAzienda, FormaGiuridicaRepository::formeGiuridicheComuniUnioni, FormaGiuridicaRepository::formeGiuridicheUniversita, FormaGiuridicaRepository::formeGiuridicheProfessionisti), true);
    }

    /**
     * @return bool
     */
    public function isPersonaFisica(): bool
    {
        return $this->getTipo() == self::PERSONA_FISICA;
    }

    /**
     * @return mixed
     */
    public function getLifnrSap() {
        return $this->lifnr_sap;
    }

    /**
     * @param mixed $lifnr_sap
     */
    public function setLifnrSap($lifnr_sap): void {
        $this->lifnr_sap = $lifnr_sap;
    }

    /**
     * @return mixed
     */
    public function getLifnrSapCreated() {
        return $this->lifnr_sap_created;
    }

    /**
     * @param mixed $lifnr_sap_created
     */
    public function setLifnrSapCreated($lifnr_sap_created): void {
        $this->lifnr_sap_created = $lifnr_sap_created;
    }

    /**
     * @return string
     */
    public function getTipoByFormaGiuridica($testo = false) {
        if ($this->getTipo() == self::ALTRI && !in_array(is_null($this->forma_giuridica) ? '1.2.10' : $this->forma_giuridica->getCodice(), array_merge(FormaGiuridicaRepository::formeGiuridicheAzienda, FormaGiuridicaRepository::formeGiuridicheComuniUnioni, FormaGiuridicaRepository::formeGiuridicheUniversita, FormaGiuridicaRepository::formeGiuridicheProfessionisti), true)) {
            return $testo ? self::TESTO_ALTRI : self::ALTRI;
        }

        if ($this->getTipo() == self::AZIENDA && in_array(is_null($this->forma_giuridica) ? '' : $this->getFormaGiuridica()->getCodice(), FormaGiuridicaRepository::formeGiuridicheAzienda, true)) {
            return $testo ? self::TESTO_AZIENDA : self::AZIENDA;
        }

        if ($this->getTipo() == self::AZIENDA && in_array(is_null($this->forma_giuridica) ? '' : $this->getFormaGiuridica()->getCodice(), FormaGiuridicaRepository::formeGiuridicheProfessionisti, true)) {
            return $testo ? self::TESTO_PROFESSIONISTA : self::PROFESSIONISTA;
        }

        if ($this->getTipo() == self::COMUNE || in_array(is_null($this->forma_giuridica) ? '' : $this->getFormaGiuridica()->getCodice(), FormaGiuridicaRepository::formeGiuridicheComuniUnioni, true)) {
            return $testo ? self::TESTO_COMUNE : self::COMUNE;
        }

        if ($this->laboratorio_ricerca == true || in_array(is_null($this->forma_giuridica) ? '' : $this->getFormaGiuridica()->getCodice(), FormaGiuridicaRepository::formeGiuridicheUniversita, true)) {
            return $testo ? self::TESTO_UNIVERSITA : self::UNIVERSITA;
        }

        if ($this->getTipo() == self::PERSONA_FISICA) {
            return $testo ? self::TESTO_PERSONA_FISICA : self::PERSONA_FISICA;
        }

        /* if($this->getTipo() == self::PROFESSIONISTA || in_array(is_null($this->forma_giuridica) ? '' : $this->forma_giuridica->getCodice(), FormaGiuridicaRepository::formeGiuridicheProfessionisti, true)) {
          return $testo ? self::TESTO_PROFESSIONISTA : self::PROFESSIONISTA;
          } */

        return $testo ? self::TESTO_ALTRI : self::ALTRI;
    }

    public function isFormaGiuridicaCoerente(): bool {
        if (\is_null($this->forma_giuridica)) {
            return true;
        }
        if ($this->laboratorio_ricerca == true && in_array($this->getFormaGiuridica()->getCodice(), FormaGiuridicaRepository::formeGiuridicheUniversita, true)) {
            return true;
        }

        if ($this->getTipo() == self::COMUNE && in_array($this->getFormaGiuridica()->getCodice(), FormaGiuridicaRepository::formeGiuridicheComuniUnioni, true)) {
            return true;
        }

        if ($this->getTipo() == self::AZIENDA && in_array($this->getFormaGiuridica()->getCodice(), FormaGiuridicaRepository::formeGiuridicheAzienda, true)) {
            return true;
        }

        if ($this->getTipo() == self::ALTRI && !in_array($this->forma_giuridica->getCodice(), array_merge(FormaGiuridicaRepository::formeGiuridicheAzienda, FormaGiuridicaRepository::formeGiuridicheComuniUnioni, FormaGiuridicaRepository::formeGiuridicheUniversita, FormaGiuridicaRepository::formeGiuridicheProfessionisti), true)) {
            return true;
        }

        if ($this->getTipo() == self::AZIENDA && in_array($this->forma_giuridica->getCodice(), FormaGiuridicaRepository::formeGiuridicheProfessionisti, true)) {
            return true;
        }

        if ($this->getTipo() == self::PERSONA_FISICA && in_array($this->forma_giuridica->getCodice(), FormaGiuridicaRepository::formeGiuridichePersonaFisica, true)) {
            return true;
        }

        return false;
    }

    public function getPivaOrCf() {
        return !is_null($this->partita_iva) ? $this->partita_iva : $this->codice_fiscale;
    }

    public function getCfOrPiva() {
        return !is_null($this->codice_fiscale) ? $this->codice_fiscale : $this->partita_iva;
    }

    public function getSedeLegale(): string {
        return "{$this->via}, {$this->civico} {$this->cap} {$this->localita} {$this->comune}";
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
     * @return bool
     */
    public function isAttivitaCulturaleCreativa() {
        if (preg_match("/^14/", $this->getCodiceAteco()) || preg_match("/^15/", $this->getCodiceAteco()) || preg_match("/^18/", $this->getCodiceAteco()) || preg_match("/^55/",
                        $this->getCodiceAteco()) || preg_match("/^58/", $this->getCodiceAteco()) || preg_match("/^59/", $this->getCodiceAteco()) || preg_match("/^60/",
                        $this->getCodiceAteco()) || preg_match("/^90/", $this->getCodiceAteco()) || preg_match("/^91/", $this->getCodiceAteco()) || preg_match("/^23.19.2/",
                        $this->getCodiceAteco()) || preg_match("/^23.70.2/", $this->getCodiceAteco()) || preg_match("/^26.20.0/", $this->getCodiceAteco()) || preg_match("/^26.30.2/",
                        $this->getCodiceAteco()) || preg_match("/^26.40.0/", $this->getCodiceAteco()) || preg_match("/^26.30.1/", $this->getCodiceAteco()) || preg_match("/^26.70.2/",
                        $this->getCodiceAteco()) || preg_match("/^28.99/", $this->getCodiceAteco()) || preg_match("/^32.12.1/", $this->getCodiceAteco()) || preg_match("/^32.12.2/",
                        $this->getCodiceAteco()) || preg_match("/^32.13.0/", $this->getCodiceAteco()) || preg_match("/^32.20.0/", $this->getCodiceAteco()) || preg_match("/^32.40.1/",
                        $this->getCodiceAteco()) || preg_match("/^32.40.2/", $this->getCodiceAteco()) || preg_match("/^46.42.1/", $this->getCodiceAteco()) || preg_match("/^46.42.4/",
                        $this->getCodiceAteco()) || preg_match("/^46.43.2/", $this->getCodiceAteco()) || preg_match("/^46.44.1/", $this->getCodiceAteco()) || preg_match("/^46.44.2/",
                        $this->getCodiceAteco()) || preg_match("/^46.47.1/", $this->getCodiceAteco()) || preg_match("/^46.48.0/", $this->getCodiceAteco()) || preg_match("/^46.49.2/",
                        $this->getCodiceAteco()) || preg_match("/^46.49.3/", $this->getCodiceAteco()) || preg_match("/^47.59.1/", $this->getCodiceAteco()) || preg_match("/^47.59.2/",
                        $this->getCodiceAteco()) || preg_match("/^47.59.3/", $this->getCodiceAteco()) || preg_match("/^47.59.6/", $this->getCodiceAteco()) || preg_match("/^47.61.0/",
                        $this->getCodiceAteco()) || preg_match("/^47.62.1/", $this->getCodiceAteco()) || preg_match("/^47.63.0/", $this->getCodiceAteco()) || preg_match("/^47.65.0/",
                        $this->getCodiceAteco()) || preg_match("/^47.71.1/", $this->getCodiceAteco()) || preg_match("/^47.71.2/", $this->getCodiceAteco()) || preg_match("/^47.71.5/",
                        $this->getCodiceAteco()) || preg_match("/^47.72.1/", $this->getCodiceAteco()) || preg_match("/^47.77.0/", $this->getCodiceAteco()) || preg_match("/^47.78.3/",
                        $this->getCodiceAteco()) || preg_match("/^47.79.1/", $this->getCodiceAteco()) || preg_match("/^47.79.2/", $this->getCodiceAteco()) || preg_match("/^47.79.4/",
                        $this->getCodiceAteco()) || preg_match("/^61.90/", $this->getCodiceAteco()) || preg_match("/^62.01/", $this->getCodiceAteco()) || preg_match("/^62.02/",
                        $this->getCodiceAteco()) || preg_match("/^62.03/", $this->getCodiceAteco()) || preg_match("/^62.09/", $this->getCodiceAteco()) || preg_match("/^63.11.1/",
                        $this->getCodiceAteco()) || preg_match("/^63.11.2/", $this->getCodiceAteco()) || preg_match("/^63.11.3/", $this->getCodiceAteco()) || preg_match("/^63.12/",
                        $this->getCodiceAteco()) || preg_match("/^63.91/", $this->getCodiceAteco()) || preg_match("/^63.99/", $this->getCodiceAteco()) || preg_match("/^71.11/",
                        $this->getCodiceAteco()) || preg_match("/^72.20/", $this->getCodiceAteco()) || preg_match("/^73.11/", $this->getCodiceAteco()) || preg_match("/^73.12/",
                        $this->getCodiceAteco()) || preg_match("/^74.10.1/", $this->getCodiceAteco()) || preg_match("/^74.10.2/", $this->getCodiceAteco()) || preg_match("/^74.10.3/",
                        $this->getCodiceAteco()) || preg_match("/^74.10.9/", $this->getCodiceAteco()) || preg_match("/^74.20.1/", $this->getCodiceAteco()) || preg_match("/^74.20.2/",
                        $this->getCodiceAteco()) || preg_match("/^77.22/", $this->getCodiceAteco()) || preg_match("/^79.90.1/", $this->getCodiceAteco()) || preg_match("/^79.90.2/",
                        $this->getCodiceAteco()) || preg_match("/^81.30/", $this->getCodiceAteco()) || preg_match("/^85.31.2/", $this->getCodiceAteco()) || preg_match("/^85.32/",
                        $this->getCodiceAteco()) || preg_match("/^85.42/", $this->getCodiceAteco()) || preg_match("/^85.51/", $this->getCodiceAteco()) || preg_match("/^85.52/",
                        $this->getCodiceAteco()) || preg_match("/^85.59.2/", $this->getCodiceAteco()) || preg_match("/^85.59.9/", $this->getCodiceAteco()) || preg_match("/^93.21/",
                        $this->getCodiceAteco()) || preg_match("/^93.29.1/", $this->getCodiceAteco()) || preg_match("/^93.29.2/", $this->getCodiceAteco()) || preg_match("/^93.29.9/",
                        $this->getCodiceAteco())) {
            return true;
        } else {
            return false;
        }
    }

    public function incarica(Persona $persona, TipoIncarico $tipoIncarico, StatoIncarico $stato): IncaricoPersona {
        $incarico = new IncaricoPersona();
        $incarico->setTipoIncarico($tipoIncarico);
        $incarico->setStato($stato);

        $incarico->setIncaricato($persona);
        $persona->addIncarichiPersone($incarico);

        $incarico->setSoggetto($this);
        $this->addIncarichiPersone($incarico);

        return $incarico;
    }

    /** torna la sede legale come oggetto Sede */
    public function getSede(): Sede {
        $indirizzo = new Indirizzo();
        $indirizzo->setCap($this->cap);
        $indirizzo->setNumeroCivico($this->civico);
        $indirizzo->setStato($this->stato);
        $indirizzo->setComune($this->comune);
        $indirizzo->setVia($this->via);
        $indirizzo->setLocalita($this->localita);
        $indirizzo->setComuneEstero($this->comuneEstero);
        $indirizzo->setProvinciaEstera($this->provinciaEstera);

        $sede = new Sede($this, $indirizzo);
        $sede->setDenominazione($this->denominazione);
        $sede->setAteco($this->codice_ateco);
        $sede->setAtecoSecondario($this->codice_ateco_secondario);

        return $sede;
    }

    public function getIncarichiProgetto() {
        $res = array();
        foreach ($this->getIncarichiPersone() as $incarico) {
            if ($incarico->getTipoIncarico()->getCodice() == 'OPERATORE_RICHIESTA') {
                $res[] = $incarico;
            }
        }
        return $res;
    }

    /**
     * @return array
     */
    public function getSoggettoFesrPerCreazioneSoggettoSap() {
        $retVal = ['esito' => true, 'soggetto' => null, 'errori' => []];
        if (empty($this->getDenominazione())) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Denominazione mancante';
        }

        if (empty($this->getCodiceFiscale()) && $this->getPartitaIva()) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Codice fiscale e Partita IVA mancanti';
        }

        if (empty($this->getVia())) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Indirizzo mancante';
        }

        if (empty($this->getComune())) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Comune mancante';
        }

        if (empty($this->getCap())) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'CAP mancante';
        }

        if (empty($this->getStato()->getDenominazione())) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Stato mancante';
        }

        if (empty($this->getEmail())) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'E-mail mancante';
        }

        if (empty($this->getEmailPec())) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'E-mail PEC mancante';
        }

        if (empty($this->getFormaGiuridica()->getCategoriaEconomicaSap())) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Categoria economica SAP mancante: ' . $this->getFormaGiuridica()->getDescrizione();
        }

        $soggetto = new Soggetto();
        if ($retVal['esito']) {
            $soggetto->setDenominazione($this->getDenominazione());
            $soggetto->setCodiceFiscale($this->getCodiceFiscale());
            $soggetto->setPartitaIva($this->getPartitaIva());
            $soggetto->setVia($this->getVia() . ($this->getCivico() ? ' ' . $this->getCivico() : ''));
            $soggetto->setComune($this->getComune());
            $soggetto->setCap($this->getCap());
            $soggetto->setStato($this->getStato());
            $soggetto->setTel($this->getTel());
            $soggetto->setFax($this->getFax());
            $soggetto->setEmail($this->getEmail());
            $soggetto->setEmailPec($this->getEmailPec());
            $soggetto->zzCatEc = $this->getFormaGiuridica()->getCategoriaEconomicaSap();

            $soggetto->flagPec = null;
            $soggetto->smtpAddr = null;
            $soggetto->regione = null;
            $soggetto->zzCodCamComm = null;
            $soggetto->zzNumLocOpere = null;
            $soggetto->zzNameLast = null;
            $soggetto->zzNameFirst = null;
            $soggetto->gbdat = null;
            $soggetto->sexkz = null;

            switch ($this->getFormaGiuridica()->getCategoriaEconomicaSap()) {
                case 211:
                case 212:
                case 213:
                case 215:
                case 220:
                case 221:
                case 222:
                case 224:
                case 223:
                case 231:
                case 232:
                case 233:
                case 410:
                case 411:
                case 430:
                case 431:
                case 531:
                    if (empty($this->getComune()->getProvincia()->getSiglaAutomobilistica())) {
                        $retVal['esito'] = false;
                        $retVal['errori'][] = 'Provincia mancante';
                    } else {
                        $soggetto->region = $this->getComune()->getProvincia()->getSiglaAutomobilistica();
                    }

                    if ($this instanceof Azienda) {
                        if (empty($this->getRea())) {
                            $retVal['esito'] = false;
                            $retVal['errori'][] = 'Codice Rea mancante';
                        } else {
                            $soggetto->zzCodCamComm = $this->getRea();
                        }
                    } else {
                        $retVal['esito'] = false;
                        $retVal['errori'][] = 'Codice Rea mancante in quanto istanza di ' . get_class($this);
                    }
                    break;

                case 100:
                case 334:
                case 350:
                case 360:
                case 362:
                case 365:
                case 510:
                case 520:
                case 530:
                case 532:
                case 601:
                case 602:
                case 604:
                case 700:
                case 741:
                case 750:
                case 800:
                case 900:
                case 910:
                case 920:
                case 930:
                    if (empty($this->getComune()->getProvincia()->getSiglaAutomobilistica())) {
                        $retVal['esito'] = false;
                        $retVal['errori'][] = 'Provincia mancante';
                    } else {
                        $soggetto->region = $this->getComune()->getProvincia()->getSiglaAutomobilistica();
                    }
                    break;

                case 310:
                case 320:
                case 330:
                    // Comuni, Comunità montane, Province
                    //$soggetto->zzNumLocOpere = '';
                    break;

                case 210:
                    $counterLR = 0;
                    if (strlen($this->getCodiceFiscale()) == 16) {
                        foreach ($this->getIncarichiPersone() as $incaricoPersona) {
                            if ($incaricoPersona->getTipoIncarico()->getCodice() == 'LR' && $incaricoPersona->getStato()->getCodice() == 'ATTIVO') {
                                $soggetto->zzNameLast = $incaricoPersona->getIncaricato()->getCognome();
                                $soggetto->zzNameFirst = $incaricoPersona->getIncaricato()->getNome();
                                $soggetto->gbdat = $incaricoPersona->getIncaricato()->getDataNascita()->format('Y-m-d');
                                if ($incaricoPersona->getIncaricato()->getSesso() == 'M') {
                                    $soggetto->sexkz = "1";
                                } else {
                                    $soggetto->sexkz = "2";
                                }
                                $counterLR++;
                            }
                        }
                    }

                    if ($counterLR = 0) {
                        $retVal['esito'] = false;
                        $retVal['errori'][] = 'Legale rappresentante mancante';
                    }

                    if ($counterLR > 1) {
                        $retVal['esito'] = false;
                        $retVal['errori'][] = $counterLR . ' legali rappresentanti presenti';
                    }

                    if (empty($this->getComune()->getProvincia()->getSiglaAutomobilistica())) {
                        $retVal['esito'] = false;
                        $retVal['errori'][] = 'Provincia mancante';
                    } else {
                        $soggetto->region = $this->getComune()->getProvincia()->getSiglaAutomobilistica();
                    }
                    break;
            }

            $retVal['soggetto'] = $soggetto;
        }

        return $retVal;
    }
    
    public function isPMI(): bool {
        return !is_null($this->dimensione_impresa) ? $this->dimensione_impresa->getCodice() != 'GRANDE' : false;
    }
    
    public function dammiProvincia() {
        if(!is_null($this->comune)) {
            return $this->comune->getProvincia()->getDenominazione();
        }elseif(!is_null($this->provinciaEstera)) {
            return $this->provinciaEstera;
        }else {
            return '-';
        }
    }
}
