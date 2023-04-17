<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RichiesteBundle\Validator\Constraints as PianoAssert;
use SoggettoBundle\Entity\DimensioneImpresa;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Validator\Constraints\ValidaLunghezza;
use BaseBundle\Validator\Constraints\ValidaLunghezzaHtml;
use SoggettoBundle\Entity\Soggetto;
use Doctrine\Common\Collections\Collection;
use AttuazioneControlloBundle\Entity\DatiBancari;
use SfingeBundle\Entity\KET;
use SfingeBundle\Entity\Driver;
use FascicoloBundle\Entity\IstanzaFascicolo;
use RichiesteBundle\Entity\VoceModalitaFinanziamento;
use RichiesteBundle\Entity\SedeOperativa;
use RichiesteBundle\Entity\DocumentoProponente;
use RichiesteBundle\Entity\Referente;
use RichiesteBundle\Entity\VocePianoCosto;

/**
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\ProponenteRepository")
 * @ORM\Table(name="proponenti",
 * 	indexes={
 *         @ORM\Index(name="idx_oggetto_richiesta_id", columns={"oggetto_richiesta_id"}),
 * 		@ORM\Index(name="idx_soggetto_id", columns={"soggetto_id"}),
 * 		@ORM\Index(name="idx_richiesta_id", columns={"richiesta_id"})
 *     })
 *     @PianoAssert\ValidaPianoCosto
 */
class Proponente extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="proponenti")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
     * 
     * @var Richiesta
     */
    private $richiesta;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\OggettoRichiesta", inversedBy="proponenti")
     * @ORM\JoinColumn(name="oggetto_richiesta_id", referencedColumnName="id", nullable=true)
     * 
     * @var OggettoRichiesta|null
     */
    private $oggetto_richiesta;

    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Soggetto", inversedBy="proponenti")
     * @ORM\JoinColumn(name="soggetto_id", referencedColumnName="id")
     */
    private $soggetto;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\DocumentoProponente", mappedBy="proponente")
     * 
     * @var DocumentoProponente[]|Collection
     */
    private $documenti_proponente;

    /**
     * @var bool
     *
     * @ORM\Column(name="mandatario", type="boolean", nullable=false, options={"default": 0})
     */
    protected $mandatario;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\Referente", mappedBy="proponente")
     * 
     * @var Collection|Referente
     */
    private $referenti;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\VocePianoCosto", mappedBy="proponente")
     * 
     * @var VocePianoCosto[]|Collection
     */
    protected $voci_piano_costo;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\VoceModalitaFinanziamento", mappedBy="proponente")
     * 
     * @var VoceModalitaFinanziamento[]|Collection
     */
    protected $voci_modalita_finanziamento;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\SedeOperativa", mappedBy="proponente")
     * 
     * @var Collection|SedeOperativa[]
     */
    protected $sedi;

    /**
     * @ORM\ManyToMany(targetEntity="SfingeBundle\Entity\Driver")
     * @ORM\JoinTable(name="proponenti_drivers")
     * 
     * @var Collection|Driver[]
     */
    protected $drivers;

    /**
     * @ORM\ManyToMany(targetEntity="SfingeBundle\Entity\KET")
     * @ORM\JoinTable(name="proponenti_kets")
     * 
     * @var Collection|KET[]
     */
    protected $kets;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\PrioritaProponente", mappedBy="proponente", cascade={"persist"})
     * 
     * @var Collection|PrioritaProponente
     */
    protected $priorita;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\AmbitoTematicoS3Proponente", mappedBy="proponente", cascade={"persist"})
     * @var Collection|AmbitoTematicoS3Proponente
     */
    protected $ambiti_tematici_s3;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Assert\NotNull(groups={"bando124", "bando126_114", "bando154", "bando180"})
     */
    protected $sede_operativa_non_attiva;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $sede_legale_come_operativa;

    /**
     * @ORM\OneToOne(targetEntity="RichiesteBundle\Entity\OccupazioneProponente", mappedBy="proponente", cascade={"persist"})
     * @var OccupazioneProponente|null
     */
    private $occupazione;

    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\DimensioneImpresa")
     * @ORM\JoinColumn
     * @Assert\NotNull(groups={"bando4, bando68", "bando126_114", "bando154", "bando174", "bando180", "bando184", "bando189"})
     * @var DimensioneImpresa
     */
    private $dimensione_impresa;

    /**
     * @Assert\Length(max="16", groups={"bando4"})
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     * @Assert\NotNull(groups={"bando4", "bando28TipoA", "bando28", "bando69", "bando190"})
     * @Assert\Range(min="700000", minMessage="Per richieste tipo A, il fatturato minimo è di 700.000 Euro", groups={"bando28TipoA"})
     * @Assert\GreaterThan(value="0", message="Il fatturato deve essere maggiore di zero", groups={"bando28TipoB", "bando69", "bando190"})
     */
    private $fatturato;

    /**
     * @Assert\Length(max="16", groups={"bando4"})
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     * @Assert\NotNull(groups={"bando4", "bando69", "bando190"})
     */
    private $bilancio;

    /**
     * @Assert\Length(max="11", groups={"bando4"})
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * @Assert\NotNull(groups={"bando4"})
     */
    private $ula;

    /*
     * Variabile di supporto nel caso di contributo totale da calcolare
     * @Assert\NotNull(groups={'contributo'})
     */
    private $totale_contributo;

    /**
     * @ORM\OneToOne(targetEntity="SoggettoBundle\Entity\SoggettoVersion", cascade={"persist"})
     * @ORM\JoinColumn(name="soggetto_version_id", referencedColumnName="id")
     */
    protected $soggetto_version;

    /**
     * @ORM\Column(name="tipo_proponente", type="string", length=50, nullable=true)
     * @Assert\NotBlank(groups={"bando5", "bando71", "bando81","bando126_122", "bando180","bando_184","bando189"})
     *
     * @var string
     */
    protected $tipo_proponente;

    /**
     * @ORM\Column(name="profilo", type="text", nullable=true)
     * @ValidaLunghezza(min=5, max=3000, groups={"bando6", "bando71"})
     * @ValidaLunghezza(min=5, max=2000, groups={"bando33"})
     * @ValidaLunghezzaHtml(min=5, max=4000, groups={"bando81"})
     *
     * @var string|null
     */
    private $profilo;

    /**
     * @ORM\Column(name="descrizione", type="text", nullable=true)
     * @ValidaLunghezzaHtml(min=5, max=4000, groups={"bando81"})
     *
     * @var string|null
     */
    private $descrizione;

    /**
     * @ORM\OneToOne(targetEntity="FascicoloBundle\Entity\IstanzaFascicolo")
     * @ORM\JoinColumn(name="istanza_fascicolo_id", referencedColumnName="id", nullable=true)
     *
     * @var IstanzaFascicolo|null
     */
    protected $istanza_fascicolo;

    /**
     * campo di appoggio per piano costi bando 15
     */
    protected $testo_piano_costi;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="rating", nullable=true)
     * @Assert\NotNull(groups={"bando28", "bando69", "bando180"})
     */
    protected $rating;

    /**
     * @var string
     * @ORM\Column(type="integer", name="stelle_rating", nullable=true)
     */
    protected $stelle_rating;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="impresa_femminile", nullable=true)
     * @Assert\NotNull(groups={"bando28", "bando69", "bando180"})
     */
    protected $impresa_femminile;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="associazione_femminile", nullable=true)
     */
    protected $associazione_femminile;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="fondazione_femminile", nullable=true)
     */
    protected $fondazione_femminile;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="impresa_giovanile", nullable=true)
     * @Assert\NotNull(groups={"bando28", "bando69", "bando180"})
     */
    protected $impresa_giovanile;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\TipoMandatario")
     * @ORM\JoinColumn(name="tipo_mandatario_id", referencedColumnName="id", nullable=true)
     */
    protected $tipo_mandatario;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $iva_recuperabile;

    /**
     * @ORM\Column(name="anno_bilancio", nullable=true)
     * @Assert\Range(min="2007", max="2018", minMessage="L'anno inserito non è valido", maxMessage="L'anno inserito non è valido", groups={"bando28TipoA", "bando28", "bando69"})
     * @Assert\NotNull(groups={"bando28TipoAMulti"})
     * @var int
     */
    protected $annoBilancio;

    /**
     * @var ProponenteProfessionista[]|Collection
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\ProponenteProfessionista", mappedBy="proponente")
     */
    protected $professionisti;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, name="costo_totale_importato_excel", nullable=true)
     */
    protected $costo_totale_importato_excel;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, name="contributo_importato_excel", nullable=true)
     */
    protected $contributo_importato_excel;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\TipologiaNaturaLaboratorio")
     * @ORM\JoinColumn(name="tipo_natura_laboratorio_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"bando71", "bando81","bando126_122"})
     */
    protected $tipo_natura_laboratorio;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\ObiettivoRealizzativo", mappedBy="proponente")
     */
    protected $obiettivi_realizzativi;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="laboratorio_accreditato", nullable=true)
     * @Assert\NotNull(groups={"bando71","bando_184"})
     */
    protected $laboratorio_accreditato;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="accreditato_innovazione", nullable=true)
     * @Assert\NotNull(groups={"bando71","bando_184"})
     */
    protected $accreditato_innovazione;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="organismo_ricerca", nullable=true)
     * @Assert\NotNull(groups={"bando71", "bando81","bando126_122","bando_184"})
     */
    protected $organismo_ricerca;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\DatiBancari", mappedBy="proponente")
     */
    protected $datiBancari;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\Intervento", mappedBy="proponente")
     */
    protected $interventi;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\TipoSettoreAttivita")
     * @ORM\JoinColumn(name="settore_attivita_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"bando65", "bando81", "bando120","bando126_114", "bando126_122"})
     */
    protected $settore_attivita;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     * @Assert\NotNull(groups={"bando61", "bando109"})
     */
    protected $importo_finanziamento_bancario;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotNull(groups={"bando61", "bando109"})
     */
    protected $durata_finanziamento_bancario;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     * @ValidaLunghezza(min=5, max=500, groups={"bando61", "bando109"})
     */
    protected $banca_finanziamento;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     * @Assert\NotNull(groups={"bando61", "bando109"})
     */
    protected $importo_garanzia_diretta;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotNull(groups={"bando109"})
     */
    protected $durata_garanzia_diretta;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     * @ValidaLunghezza(min=5, max=500, groups={"bando61", "bando109"})
     */
    protected $nome_confidi;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Assert\NotNull(groups={"bando69", "bando68", "bando114","bando189"})
     */
    protected $comunita_montana;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Assert\NotNull(groups={"bando114"})
     */
    protected $area107c;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\TipoAiutoProponente")
     * @ORM\JoinColumn(name="tipo_aiuto_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"bando126_114","bando126_122"})
     */
    protected $tipo_aiuto;

    /**
     * @ORM\Column(type="boolean", name="aiuto", nullable=true)
     * @Assert\NotNull(groups={"bando71","bando81","bando126_122","bando_184"})
     */
    protected $aiuto;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     * @Assert\NotNull(groups={"bando103"})
     */
    private $capitale_netto;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     */
    private $aumento_capitale;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     */
    private $capitale_sociale_1;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     */
    private $capitale_sociale_2;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     * @Assert\NotNull(groups={"bando103"})
     */
    private $oneri_finanziari;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\TipoFiliera")
     * @ORM\JoinColumn(name="filiera_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"bando103"})
     */
    protected $filiera;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Assert\NotNull(groups={"bando_184"})
     */
    protected $aiuto_organismo;

    /**
     * Proponente constructor.
     */
    public function __construct(?Richiesta $richiesta = null) {
        $this->documenti_proponente = new ArrayCollection();
        $this->referenti = new ArrayCollection();
        $this->mandatario = false;
        $this->voci_piano_costo = new ArrayCollection();
        $this->voci_modalita_finanziamento = new ArrayCollection();
        $this->priorita = new ArrayCollection();
        $this->professionisti = new ArrayCollection();
        $this->datiBancari = new ArrayCollection();
        $this->interventi = new ArrayCollection();
        $this->sedi = new ArrayCollection();
        $this->obiettivi_realizzativi = new ArrayCollection();
        $this->richiesta = $richiesta;
        $this->ambiti_tematici_s3 = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getCostoTotaleImportatoExcel() {
        return $this->costo_totale_importato_excel;
    }

    public function getContributoImportatoExcel() {
        return $this->contributo_importato_excel;
    }

    public function getSoggetto(): ?Soggetto {
        return $this->soggetto;
    }

    public function getSoggettoMandatario(): Soggetto {
        return $this->getRichiesta()->getSoggetto();
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setSoggetto(?Soggetto $soggetto): self {
        $this->soggetto = $soggetto;
        return $this;
    }

    /**
     * @return DocumentoProponente[]|Collection
     */
    public function getDocumentiproponente(): Collection {
        return $this->documenti_proponente;
    }

    public function setDocumentiproponente(Collection $documenti_proponente): self {
        $this->documenti_proponente = $documenti_proponente;
        return $this;
    }

    public function getRichiesta(): ?Richiesta {
        return $this->richiesta;
    }

    public function setRichiesta(?Richiesta $richiesta): Proponente {
        $this->richiesta = $richiesta;
        return $this;
    }

    public function getOggettoRichiesta(): ?OggettoRichiesta {
        return $this->oggetto_richiesta;
    }

    public function setOggettoRichiesta(OggettoRichiesta $oggetto_richiesta): self {
        $this->oggetto_richiesta = $oggetto_richiesta;
        return $this;
    }

    public function getMandatario(): bool {
        return $this->mandatario;
    }

    public function setMandatario(bool $mandatario): self {
        $this->mandatario = $mandatario;
        return $this;
    }

    /**
     * @return Referente[]|Collection
     */
    public function getReferenti(): Collection {
        return $this->referenti;
    }

    public function setReferenti(Collection $referenti): self {
        $this->referenti = $referenti;
        return $this;
    }

    /**
     * @return Collection|VocePianoCosto[]
     */
    public function getVociPianoCosto(): Collection {
        return $this->voci_piano_costo;
    }

    public function setVociPianoCosto(Collection $voci_piano_costo): self {
        $this->voci_piano_costo = $voci_piano_costo;

        return $this;
    }

    /**
     * @return Collection|VoceModalitaFinanziamento[]
     */
    public function getVociModalitaFinanziamento(): Collection {
        return $this->voci_modalita_finanziamento;
    }

    public function setVociModalitaFinanziamento(Collection $voci_modalita_finanziamento): self {
        $this->voci_modalita_finanziamento = $voci_modalita_finanziamento;

        return $this;
    }

    public function addDocumentiProponente(DocumentoProponente $documentiProponente): self {
        $this->documenti_proponente[] = $documentiProponente;

        return $this;
    }

    public function removeDocumentiProponente(DocumentoProponente $documentiProponente): void {
        $this->documenti_proponente->removeElement($documentiProponente);
    }

    public function addReferenti(Referente $referenti): self {
        $this->referenti[] = $referenti;

        return $this;
    }

    public function removeReferenti(Referente $referenti): void {
        $this->referenti->removeElement($referenti);
    }

    public function addVociPianoCosto(VocePianoCosto $vociPianoCosto): self {
        $this->voci_piano_costo[] = $vociPianoCosto;

        return $this;
    }

    public function removeVociPianoCosto(VocePianoCosto $vociPianoCosto): void {
        $this->voci_piano_costo->removeElement($vociPianoCosto);
    }

    public function addVociModalitaFinanziamento(VoceModalitaFinanziamento $vociModalitaFinanziamento): self {
        $this->voci_modalita_finanziamento[] = $vociModalitaFinanziamento;

        return $this;
    }

    public function removeVociModalitaFinanziamento(VoceModalitaFinanziamento $vociModalitaFinanziamento): void {
        $this->voci_modalita_finanziamento->removeElement($vociModalitaFinanziamento);
    }

    public function addSedi(SedeOperativa $sedi): self {
        $this->sedi[] = $sedi;

        return $this;
    }

    public function removeSedi(SedeOperativa $sedi): void {
        $this->sedi->removeElement($sedi);
    }

    /**
     * Get sedi
     *
     * @return Collection|SedeOperativa[]
     */
    public function getSedi(): Collection {
        return $this->sedi;
    }

    /**
     * @return Driver[]|Collection
     */
    public function getDrivers(): Collection {
        return $this->drivers;
    }

    /**
     * @return KET[]|Collection
     */
    public function getKets(): Collection {
        return $this->kets;
    }

    /**
     * @var PrioritaProponente[]|Collection
     */
    public function getPriorita(): Collection {
        return $this->priorita;
    }

    public function setDrivers(Collection $drivers): self {
        $this->drivers = $drivers;

        return $this;
    }

    public function setKets(Collection $kets): self {
        $this->kets = $kets;

        return $this;
    }

    public function setPriorita(Collection $priorita): self {
        $this->priorita = $priorita;

        return $this;
    }

    public function addPriorita(PrioritaProponente $priorita): self {
        $priorita->setProponente($this);
        $this->priorita->add($priorita);

        return $this;
    }

    public function addAmbitoTematicoS3(AmbitoTematicoS3Proponente $ambitoTematicoS3Proponente): self {
        $ambitoTematicoS3Proponente->setProponente($this);
        $this->ambiti_tematici_s3->add($ambitoTematicoS3Proponente);
        return $this;
    }

    public function getSedeOperativaNonAttiva(): ?bool {
        return $this->sede_operativa_non_attiva;
    }

    public function getSedeLegaleComeOperativa(): ?bool {
        return $this->sede_legale_come_operativa;
    }

    public function setSedeOperativaNonAttiva(?bool $sede_operativa_non_attiva) {
        $this->sede_operativa_non_attiva = $sede_operativa_non_attiva;
    }

    public function setSedeLegaleComeOperativa(?bool $sede_legale_come_operativa) {
        $this->sede_legale_come_operativa = $sede_legale_come_operativa;
    }

    public function getOccupazione(): ?OccupazioneProponente {
        return $this->occupazione;
    }

    public function getDimensioneImpresa() {
        return $this->dimensione_impresa;
    }

    public function getFatturato() {
        return $this->fatturato;
    }

    public function getBilancio() {
        return $this->bilancio;
    }

    public function getUla() {
        return $this->ula;
    }

    public function setOccupazione(?OccupazioneProponente $occupazione) {
        $this->occupazione = $occupazione;
        $this->occupazione->setProponente($this);
    }

    public function setDimensioneImpresa($dimensione_impresa) {
        $this->dimensione_impresa = $dimensione_impresa;
    }

    public function setFatturato($fatturato) {
        $this->fatturato = $fatturato;
    }

    public function setBilancio($bilancio) {
        $this->bilancio = $bilancio;
    }

    public function setUla($ula) {
        $this->ula = $ula;
    }

    public function getTotaleContributo() {
        return $this->totale_contributo;
    }

    public function setTotaleContributo($totale_contributo) {
        $this->totale_contributo = $totale_contributo;
    }

    public function getSoggettoVersion() {
        return $this->soggetto_version;
    }

    public function setSoggettoVersion($soggetto_version) {
        $this->soggetto_version = $soggetto_version;
    }

    public function hasPianoCosto() {
        return count($this->getVociPianoCosto()) > 0;
    }

    public function getTipoProponente() {
        return $this->tipo_proponente;
    }

    public function setTipoProponente($tipo_proponente) {
        $this->tipo_proponente = $tipo_proponente;
    }

    public function getRating() {
        return $this->rating;
    }

    public function getStelleRating() {
        return $this->stelle_rating;
    }

    public function setRating($rating) {
        $this->rating = $rating;
    }

    public function setStelleRating($stelle_rating) {
        $this->stelle_rating = $stelle_rating;
    }

    public function getImpresaFemminile() {
        return $this->impresa_femminile;
    }

    public function getAssociazioneFemminile() {
        return $this->associazione_femminile;
    }

    public function getImpresaGiovanile() {
        return $this->impresa_giovanile;
    }

    public function setImpresaFemminile($impresa_femminile) {
        $this->impresa_femminile = $impresa_femminile;
    }

    public function setAssociazioneFemminile($associazione_femminile) {
        $this->associazione_femminile = $associazione_femminile;
    }

    public function setCostoTotaleImportatoExcel($costo_totale_importato_excel) {
        $this->costo_totale_importato_excel = $costo_totale_importato_excel;
    }

    public function setContributoImportatoExcel($contributo_importato_excel) {
        $this->contributo_importato_excel = $contributo_importato_excel;
    }

    public function setImpresaGiovanile($impresa_giovanile) {
        $this->impresa_giovanile = $impresa_giovanile;
    }

    public function getFondazioneFemminile() {
        return $this->fondazione_femminile;
    }

    public function setFondazioneFemminile($fondazione_femminile) {
        $this->fondazione_femminile = $fondazione_femminile;
    }

    public function setProfilo(?string $profilo): self {
        $this->profilo = $profilo;

        return $this;
    }

    public function getProfilo(): ?string {
        return $this->profilo;
    }

    public function getIstanzaFascicolo(): ?IstanzaFascicolo {
        return $this->istanza_fascicolo;
    }

    public function setIstanzaFascicolo(?IstanzaFascicolo $istanza_fascicolo) {
        $this->istanza_fascicolo = $istanza_fascicolo;
    }

    public function addDriver(Driver $drivers): self {
        $this->drivers[] = $drivers;

        return $this;
    }

    public function removeDriver(Driver $drivers): void {
        $this->drivers->removeElement($drivers);
    }

    public function addKet(KET $kets): self {
        $this->kets[] = $kets;

        return $this;
    }

    public function removeKet(KET $kets): void {
        $this->kets->removeElement($kets);
    }

    public function addPrioritum(PrioritaProponente $priorita): self {
        $this->priorita[] = $priorita;

        return $this;
    }

    public function removePrioritum(PrioritaProponente $priorita): void {
        $this->priorita->removeElement($priorita);
    }

    public function getTestoPianoCosti() {
        return $this->testo_piano_costi;
    }

    public function setTestoPianoCosti($testo_piano_costi) {
        $this->testo_piano_costi = $testo_piano_costi;
        return $this;
    }

    public function getTipoMandatario() {
        return $this->tipo_mandatario;
    }

    public function setTipoMandatario($tipo_mandatario) {
        $this->tipo_mandatario = $tipo_mandatario;
    }

    public function getIvaRecuperabile() {
        return $this->iva_recuperabile;
    }

    public function setIvaRecuperabile($iva_recuperabile) {
        $this->iva_recuperabile = $iva_recuperabile;
    }

    public function getTipoNaturaLaboratorio(): ?TipologiaNaturaLaboratorio {
        return $this->tipo_natura_laboratorio;
    }

    public function setTipoNaturaLaboratorio($tipo_natura_laboratorio) {
        $this->tipo_natura_laboratorio = $tipo_natura_laboratorio;
    }

    public function __toString(): string {
        $denominazione = $this->soggetto->getDenominazione();
        return empty($denominazione) ? $this->soggetto->getAcronimoLaboratorio() : $this->soggetto->getDenominazione();
    }

    public function getObiettiviRealizzativi(): Collection {
        return $this->obiettivi_realizzativi;
    }

    public function setObiettiviRealizzativi(Collection $obiettivi_realizzativi): self {
        $this->obiettivi_realizzativi = $obiettivi_realizzativi;

        return $this;
    }

    public function getAcronimoLaboratorio() {
        return $this->soggetto->getAcronimoLaboratorio();
    }

    public function getLaboratorioAccreditato() {
        return $this->laboratorio_accreditato;
    }
    
    public function isLaboratorioAccreditato() {
        return $this->laboratorio_accreditato == true;
    }

    public function getOrganismoRicerca() {
        return $this->organismo_ricerca;
    }
    
    public function isOrganismoRicerca() {
        return $this->organismo_ricerca == true;
    }

    public function setLaboratorioAccreditato($laboratorio_accreditato) {
        $this->laboratorio_accreditato = $laboratorio_accreditato;
    }

    public function setOrganismoRicerca($organismo_ricerca) {
        $this->organismo_ricerca = $organismo_ricerca;
    }

    /**
     * @return ProponenteProfessionista[]|Collection
     */
    public function getProfessionisti() {
        return $this->professionisti;
    }

    public function setProfessionisti($value) {
        $this->professionisti = $value;
    }

    public function getAnnoBilancio() {
        return $this->annoBilancio;
    }

    public function setAnnoBilancio($annoBilancio) {
        $this->annoBilancio = $annoBilancio;
    }

    public function getDenominazione(): string {
        $denominazione = "";
        if (!is_null($this->soggetto_version)) {
            $denominazione = $this->soggetto_version->__toString();
        }

        if ("" == $denominazione) {
            $denominazione = $this->soggetto->__toString();
        }

        if ("" == $denominazione) {
            $denominazione = $this->soggetto->getAcronimoLaboratorio();
        }

        return $denominazione;
    }

    public function getDenominazioneAcronimo(): string {
        $denominazione = $this->soggetto->getAcronimoLaboratorio();

        return \is_null($denominazione) || empty($denominazione) ? $this->soggetto->__toString() : $denominazione;
    }

    public function isMandatario(): bool {
        return true == $this->mandatario;
    }

    public function getDatiBancari() {
        return $this->datiBancari;
    }

    public function setDatiBancari($datiBancari) {
        $this->datiBancari = $datiBancari;
    }

    public function getInterventi() {
        return $this->interventi;
    }

    public function setInterventi($interventi) {
        $this->interventi = $interventi;
    }

    public function getImportoFinanziamentoBancario() {
        return $this->importo_finanziamento_bancario;
    }

    public function getDurataFinanziamentoBancario() {
        return $this->durata_finanziamento_bancario;
    }

    public function getBancaFinanziamento() {
        return $this->banca_finanziamento;
    }

    public function getImportoGaranziaDiretta() {
        return $this->importo_garanzia_diretta;
    }

    public function setImportoFinanziamentoBancario($importo_finanziamento_bancario) {
        $this->importo_finanziamento_bancario = $importo_finanziamento_bancario;
    }

    public function setDurataFinanziamentoBancario($durata_finanziamento_bancario) {
        $this->durata_finanziamento_bancario = $durata_finanziamento_bancario;
    }

    public function setBancaFinanziamento($banca_finanziamento) {
        $this->banca_finanziamento = $banca_finanziamento;
    }

    public function setImportoGaranziaDiretta($importo_garanzia_diretta) {
        $this->importo_garanzia_diretta = $importo_garanzia_diretta;
    }

    /**
     * @return mixed
     */
    public function getDurataGaranziaDiretta() {
        return $this->durata_garanzia_diretta;
    }

    /**
     * @param mixed $durata_garanzia_diretta
     */
    public function setDurataGaranziaDiretta($durata_garanzia_diretta): void {
        $this->durata_garanzia_diretta = $durata_garanzia_diretta;
    }

    public function getNomeConfidi() {
        return $this->nome_confidi;
    }

    public function setNomeConfidi($nome_confidi) {
        $this->nome_confidi = $nome_confidi;
    }

    public function addProfessionisti(ProponenteProfessionista $professionisti): self {
        $this->professionisti[] = $professionisti;

        return $this;
    }

    public function removeProfessionisti(ProponenteProfessionista $professionisti): void {
        $this->professionisti->removeElement($professionisti);
    }

    public function addObiettiviRealizzativi(ObiettivoRealizzativo $obiettiviRealizzativi): self {
        $this->obiettivi_realizzativi[] = $obiettiviRealizzativi;

        return $this;
    }

    public function removeObiettiviRealizzativi(ObiettivoRealizzativo $obiettiviRealizzativi): void {
        $this->obiettivi_realizzativi->removeElement($obiettiviRealizzativi);
    }

    public function addDatiBancari(DatiBancari $datiBancari): self {
        $this->datiBancari[] = $datiBancari;

        return $this;
    }

    public function removeDatiBancari(DatiBancari $datiBancari): void {
        $this->datiBancari->removeElement($datiBancari);
    }

    public function addInterventi(Intervento $interventi): self {
        $this->interventi[] = $interventi;

        return $this;
    }

    public function removeInterventi(Intervento $interventi): void {
        $this->interventi->removeElement($interventi);
    }

    public function getSettoreAttivita() {
        return $this->settore_attivita;
    }

    public function setSettoreAttivita($settore_attivita) {
        $this->settore_attivita = $settore_attivita;
    }

    /**
     * @param bool $comunitaMontana
     * @return Proponente
     */
    public function setComunitaMontana(bool $comunitaMontana): self {
        $this->comunita_montana = $comunitaMontana;

        return $this;
    }

    /**
     * @return bool
     */
    public function getComunitaMontana(): ?bool {
        return $this->comunita_montana;
    }

    public function ordinaVociPianoCosto(): void {
        $iterator = $this->voci_piano_costo->getIterator();
        $iterator->uasort(function ($a, $b) {
            return $a->getPianoCosto()->getOrdinamento() - $b->getPianoCosto()->getOrdinamento();
        });
        $resultArray = (array) \iterator_to_array($iterator);

        $this->voci_piano_costo = new ArrayCollection($resultArray);
    }

    /*
     * Aggiungo queste variabili di appoggio per la gestione delle procedure particolari (ass. tec. e acquisizioni)
     * in modo da poter salvare i dati in istruttoria e gestire gli impegni
     * INIZIO VARIABILI APPOGGIO PROCEDURE PARTICOLARI
     */

    protected $contributo;
    private $impegno;

    public function getContributo() {
        return $this->contributo;
    }

    public function getImpegno() {
        return $this->impegno;
    }

    public function setContributo($contributo) {
        $this->contributo = $contributo;
    }

    public function setImpegno($impegno) {
        $this->impegno = $impegno;
    }

    /*
     * FINE VARIABILI APPOGGIO PROCEDURE PARTICOLARI
     */

    public function setAccreditatoInnovazione(?bool $accreditatoInnovazione): self {
        $this->accreditato_innovazione = $accreditatoInnovazione;

        return $this;
    }

    public function getAccreditatoInnovazione(): ?bool {
        return $this->accreditato_innovazione;
    }
    
    public function isAccreditatoInnovazione(): ?bool {
        return $this->accreditato_innovazione == true;
    }

    public function setAiuto(?bool $aiuto): self {
        $this->aiuto = $aiuto;

        return $this;
    }

    public function getAiuto(): ?bool {
        return $this->aiuto;
    }

    public function setTipoAiuto(?TipoAiutoProponente $tipoAiuto): self {
        $this->tipo_aiuto = $tipoAiuto;

        return $this;
    }

    public function hasAiuto(): ?bool {
        return $this->aiuto == true;
    }

    public function getTipoAiuto(): ?TipoAiutoProponente {
        return $this->tipo_aiuto;
    }

    public function setDescrizione(?string $descrizione): self {
        $this->descrizione = $descrizione;
        return $this;
    }

    public function getDescrizione(): ?string {
        return $this->descrizione;
    }

    public function getTotalePianoCosti(): float {
        $vociTot = $this->voci_piano_costo->filter(function (VocePianoCosto $v) {
            return $v->getPianoCosto()->getCodice() == 'TOT';
        });
        $tot = \array_reduce($vociTot->toArray(), function ($carry, VocePianoCosto $v) {
            return $carry + $v->getTotale();
        }, 0.0);

        return $tot;
    }

    public function getTotalePianoCostiPerAnno($anno): float {
        $vociTot = $this->voci_piano_costo->filter(function (VocePianoCosto $v) {
            return $v->getPianoCosto()->getCodice() == 'TOT';
        });
        $tot = \array_reduce($vociTot->toArray(), function ($carry, VocePianoCosto $v) use ($anno) {
            return $carry + $v->getTotalePerAnno($anno);
        }, 0.0);

        return $tot;
    }

    public function getCapitaleNetto() {
        return $this->capitale_netto;
    }

    public function getAumentoCapitale() {
        return $this->aumento_capitale;
    }

    public function getCapitaleSociale1() {
        return $this->capitale_sociale_1;
    }

    public function getCapitaleSociale2() {
        return $this->capitale_sociale_2;
    }

    public function getOneriFinanziari() {
        return $this->oneri_finanziari;
    }

    public function setCapitaleNetto($capitale_netto) {
        $this->capitale_netto = $capitale_netto;
    }

    public function setAumentoCapitale($aumento_capitale) {
        $this->aumento_capitale = $aumento_capitale;
    }

    public function setCapitaleSociale1($capitale_sociale_1) {
        $this->capitale_sociale_1 = $capitale_sociale_1;
    }

    public function setCapitaleSociale2($capitale_sociale_2) {
        $this->capitale_sociale_2 = $capitale_sociale_2;
    }

    public function setOneriFinanziari($oneri_finanziari) {
        $this->oneri_finanziari = $oneri_finanziari;
    }

    public function getFiliera(): ?TipoFiliera {
        return $this->filiera;
    }

    public function setFiliera(?TipoFiliera $filiera) {
        $this->filiera = $filiera;
    }

    public function getReferentiMail() {
        $mail = array();
        foreach ($this->referenti as $ref) {
            $mail[] = $ref->getPersona()->getEmailPrincipale();
        }
        return $mail;
    }

    public function setArea107c(?bool $area107c): self {
        $this->area107c = $area107c;

        return $this;
    }

    public function getArea107c(): ?bool {
        return $this->area107c;
    }

    public function isPMI(): bool {
        return !is_null($this->dimensione_impresa) ? $this->dimensione_impresa->getCodice() != 'GRANDE' : false;
    }

    public function isPiccolaMicro(): bool {
        return !is_null($this->dimensione_impresa) ? (($this->dimensione_impresa->getCodice() == 'PICCOLA') || ($this->dimensione_impresa->getCodice() == 'MICRO')) : false;
    }

    public function isMedia(): bool {
        return !is_null($this->dimensione_impresa) ? $this->dimensione_impresa->getCodice() == 'MEDIA' : false;
    }

    public function isGrande(): bool {
        return !is_null($this->dimensione_impresa) ? $this->dimensione_impresa->getCodice() == 'GRANDE' : false;
    }

    public function isPMIControlloLoco() {
        $res = '-';
        if (!is_null($this->dimensione_impresa)) {
            $res = $this->isPMI() == true ? 'Si' : 'No';
        } elseif (is_null($this->dimensione_impresa)) {
            if (!is_null($this->soggetto->getDimensioneImpresa())) {
                $res = $this->soggetto->isPMI() == true ? 'Si' : 'No';
            }
        } else {
            $res = '-';
        }
        return $res;
    }

    /**
     * @return Collection
     */
    public function getAmbitiTematiciS3(): Collection {
        return $this->ambiti_tematici_s3;
    }

    /**
     * @param Collection|AmbitoTematicoS3Proponente $ambiti_tematici_s3
     */
    public function setAmbitiTematiciS3(Collection $ambiti_tematici_s3): void {
        $this->ambiti_tematici_s3 = $ambiti_tematici_s3;
    }

    public function hasFemminile() {
        return $this->impresa_femminile == true;
    }

    public function hasGiovanile() {
        return $this->impresa_giovanile == true;
    }

    public function hasRating() {
        return $this->rating == true;
    }

    public function hasSede107() {
        foreach ($this->sedi as $s) {
            if ($s->hasSede107()) {
                return true;
            }
        }
        return false;
    }

    public function hasSedeMontana() {
        foreach ($this->sedi as $s) {
            if ($s->hasSedeMontana()) {
                return true;
            }
        }
        return false;
    }

    public function hasAumentoCapitale180A() {
        return (!is_null($this->aumento_capitale) && $this->aumento_capitale != 0) ||
            (!is_null($this->capitale_sociale_1) && $this->capitale_sociale_1 != 0) ||
            (!is_null($this->capitale_sociale_2) && $this->capitale_sociale_2 != 0);
    }

    public function hasAumentoCapitale180B() {
        return !is_null($this->importo_garanzia_diretta) && $this->importo_garanzia_diretta != 0;
    }

    public function hasFb180() {
        return !is_null($this->importo_finanziamento_bancario) && $this->importo_finanziamento_bancario != 0;
    }

    public function hasMezziPropri180() {
        return !is_null($this->importo_garanzia_diretta) && $this->importo_garanzia_diretta != 0;
    }

    public function setAiutoOrganismo(?bool $aiuto_organismo): self {
        $this->aiuto_organismo = $aiuto_organismo;

        return $this;
    }

    public function getAiutoOrganismo(): ?bool {
        return $this->aiuto_organismo;
    }

    public function hasAiutoOrganismo(): ?bool {
        return $this->aiuto_organismo == true;
    }

    public function isDirittoPrivato(): bool {
        $arrayPrivati = array('PRIVATO_NO_ECO', 'PRIVATO_ECO', 'PRIVATO');
        return in_array($this->tipo_proponente, $arrayPrivati);
    }

    public function getTipoProponenteString($codice) {
        $tipologie = array(
            'PRIVATO_NO_ECO' => 'Soggetto di diritto privato che NON svolge attività economica',
            'PRIVATO_ECO' => 'Soggetto di diritto privato che svolge attività economica',
            'PUBBLICO_NO_ECO' => 'Soggetto di diritto pubblico che NON svolge attività economica',
            'PUBBLICO_ECO' => 'Soggetto di diritto pubblico che svolge attività economica',
            'PRIVATO' => 'Soggetto di diritto privato',
            'PUBBLICO' => 'Soggetto di diritto pubblico',
        );

        return $tipologie[$codice];
    }

}
