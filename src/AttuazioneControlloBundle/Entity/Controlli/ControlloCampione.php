<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Controlli\ControlloCampioneRepository")
 * @ORM\Table(name="controlli_campione")
 */
class ControlloCampione extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto", mappedBy="campione", cascade={"persist", "remove"})
     * @var Collection|ControlloProgetto[]
     */
    protected $controlli;

    /**
     * @var string
     * @Assert\NotNUll()
     * @ORM\Column(name="descrizione", type="string", length=1000, nullable=false)
     */
    protected $descrizione;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime|null
     */
    protected $data_inizio;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime|null
     */
    protected $data_termine;

    /**
     * @var string
     * @Assert\NotNUll()
     * @ORM\Column(name="tipo", type="string", length=50, nullable=false)
     */
    protected $tipo;
    
    /**
     * @var string
     * @Assert\NotNUll()
     * @ORM\Column(name="tipo_controllo", type="string", length=50, nullable=false)
     */
    protected $tipo_controllo;

    /**
     * @ORM\Column(type="array", name="pre_campione", nullable=true)
     */
    protected $pre_campione;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Controlli\DocumentoControlloCampione", mappedBy="controllo_campione", cascade={"persist"})
     */
    protected $documenti_controllo;
    //variabili di supporto
    protected $percentuale_coperta;
    protected $imprese_campionate;
    protected $imprese_controllate;
    protected $spesa_controllata;
    protected $spesa_irregolare;
    protected $rettifiche;
    protected $cl_non_ammesse;
    protected $cl_rend_ammesse;
    protected $cl_loco_ammesse;
    protected $universo;
    protected $campioni_estesi;
    protected $file;

    function getId() {
        return $this->id;
    }

    function getControlli() {
        return $this->controlli;
    }

    function getDescrizione(): ?string {
        return $this->descrizione;
    }

    function setId($id): void {
        $this->id = $id;
    }

    function setControlli($controlli) {
        $this->controlli = $controlli;
    }

    function setDescrizione(string $descrizione): void {
        $this->descrizione = $descrizione;
    }

    function getDataInizio(): ?\DateTime {
        return $this->data_inizio;
    }

    function getDataTermine(): ?\DateTime {
        return $this->data_termine;
    }

    function setDataInizio(?\DateTime $data_inizio): void {
        $this->data_inizio = $data_inizio;
    }

    function setDataTermine(?\DateTime $data_termine): void {
        $this->data_termine = $data_termine;
    }

    function getTipo() {
        return $this->tipo;
    }

    function getPreCampione() {
        return $this->pre_campione;
    }

    function setTipo(string $tipo) {
        $this->tipo = $tipo;
    }

    function setPreCampione($pre_campione): void {
        $this->pre_campione = $pre_campione;
    }

    public function __construct() {
        $this->controlli = new ArrayCollection();
    }

    function getDocumentiControllo() {
        return $this->documenti_controllo;
    }

    function getPercentualeCoperta() {
        return $this->percentuale_coperta;
    }

    function getImpreseCampionate() {
        return $this->imprese_campionate;
    }

    function getImpreseControllate() {
        return $this->imprese_controllate;
    }

    function getSpesaControllata() {
        return $this->spesa_controllata;
    }

    function getSpesaIrregolare() {
        return $this->spesa_irregolare;
    }

    function getRettifiche() {
        return $this->rettifiche;
    }

    function getCLnonAmmesse() {
        return $this->cl_non_ammesse;
    }

    function getClRendAmmesse() {
        return $this->cl_rend_ammesse;
    }

    function getClLocoAmmesse() {
        return $this->cl_loco_ammesse;
    }

    function setDocumentiControllo($documenti_controllo): void {
        $this->documenti_controllo = $documenti_controllo;
    }

    function setPercentualeCoperta($percentuale_coperta): void {
        $this->percentuale_coperta = $percentuale_coperta;
    }

    function setImpreseCampionate($imprese_campionate): void {
        $this->imprese_campionate = $imprese_campionate;
    }

    function setImpreseControllate($imprese_controllate): void {
        $this->imprese_controllate = $imprese_controllate;
    }

    function setSpesaCcontrollata($spesa_controllata): void {
        $this->spesa_controllata = $spesa_controllata;
    }

    function setSpesaIrregolare($spesa_irregolare): void {
        $this->spesa_irregolare = $spesa_irregolare;
    }

    function setRettifiche($rettifiche): void {
        $this->rettifiche = $rettifiche;
    }

    function setClNonAmmesse($cl_non_ammesse): void {
        $this->cl_non_ammesse = $cl_non_ammesse;
    }

    function setClRendAmmesse($cl_rend_ammesse): void {
        $this->cl_rend_ammesse = $cl_rend_ammesse;
    }

    function setClLocoAmmesse($cl_loco_ammesse): void {
        $this->cl_loco_ammesse = $cl_loco_ammesse;
    }

    function getUniverso() {
        return $this->universo;
    }

    function setUniverso($universo): void {
        $this->universo = $universo;
    }

    function getCampioniEstesi() {
        return $this->campioni_estesi;
    }

    function setCampioni_estesi($campioni_estesi): void {
        $this->campioni_estesi = $campioni_estesi;
    }

    public function addCampioneEsteso($controllo) {
        $this->campioni_estesi[] = $controllo;
        $controllo->setCampione($this);
    }

    public function addControllo($controllo) {
        $this->controlli->add($controllo);
        $controllo->setCampione($this);
    }

    function getFile() {
        return $this->file;
    }

    function setFile($file): void {
        $this->file = $file;
    }

    public function addDocumentoControllo($documento_controllo) {
        $documento_controllo->setControlloCampione($this);
        $this->documenti_controllo->add($documento_controllo);
    }
    
    function getTipoControllo():? string {
        return $this->tipo_controllo;
    }

    function setTipoControllo(string $tipo_controllo): void {
        $this->tipo_controllo = $tipo_controllo;
    }

}
