<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\Collection;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Indirizzo;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Controlli\ControlloProgettoRepository")
 * @ORM\Table(name="controlli_progetti")
 */
class ControlloProgetto extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="controlli")
     * @ORM\JoinColumn(nullable=false)
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Controlli\DocumentoControllo", mappedBy="controllo_progetto", cascade={"persist"})
     * @var Collection|DocumentoControllo[]
     */
    protected $documenti_controllo;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ValutazioneChecklistControllo", mappedBy="controllo_progetto", cascade={"persist"})
     * @var Collection|ValutazioneChecklistControllo[]
     */
    protected $valutazioni_checklist;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Controlli\TipoEsitoControllo")
     * @ORM\JoinColumn(nullable=true)
     * @var TipoEsitoControllo|null
     */
    protected $esito;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $note;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $note_sopralluogo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_esito;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $note_esito;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_inizio_controlli;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_validazione;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     */
    protected $controllo_fase_desk;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $osservazioni_fase_desk;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $acquisita_fase_spr;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $richiesta_fase_spr;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     */
    protected $tipo_sede_fase_spr;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $conclusioni_fase_spr;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $osservazioni_ben_fase_spr;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     */
    protected $spese_ammesse;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     */
    protected $spese_rivalutazione;

    /**
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=true)
     */
    protected $spese_non_ammissibili;

    /**
     * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\Indirizzo", cascade={"persist"})
     * @ORM\JoinColumn(name="indirizzo_id", referencedColumnName="id", nullable=true)
     * @Assert\Valid
     * @var Indirizzo|null
     */
    private $indirizzo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_controllo;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ControlloCampione", inversedBy="controlli")
     * @ORM\JoinColumn(nullable=true)
     * @var ControlloCampione
     */
    protected $campione;

    /**
     * @var string
     *
     * @ORM\Column(name="tipologia", type="string", length=50, nullable=true)
     */
    protected $tipologia;

    public function __construct(Richiesta $richiesta = null) {
        $this->documenti_controllo = new ArrayCollection();
        $this->valutazioni_checklist = new ArrayCollection();

        $this->richiesta = $richiesta;
    }

    public function getId() {
        return $this->id;
    }

    public function getDocumentiControllo(): Collection {
        return $this->documenti_controllo;
    }

    public function getValutazioniChecklist(): Collection {
        return $this->valutazioni_checklist;
    }

    public function getEsito(): ?TipoEsitoControllo {
        return $this->esito;
    }

    public function getNote(): ?string {
        return $this->note;
    }

    public function getDataEsito(): ?\DateTime {
        return $this->data_esito;
    }

    public function setDocumentiControllo(Collection $documenti_controllo): self {
        $this->documenti_controllo = $documenti_controllo;
        return $this;
    }

    public function setValutazioniChecklist(Collection $valutazioni_checklist): self {
        $this->valutazioni_checklist = $valutazioni_checklist;
        return $this;
    }

    public function setEsito(?TipoEsitoControllo $esito): self {
        $this->esito = $esito;
        return $this;
    }

    public function setNote(?string $note): self {
        $this->note = $note;
        return $this;
    }

    public function setDataEsito(?\DateTime $data_esito): self {
        $this->data_esito = $data_esito;
        return $this;
    }

    public function addValutazioneChecklist(ValutazioneChecklistControllo $valutazione_checklist): self {
        $this->valutazioni_checklist->add($valutazione_checklist);
        return $this;
    }

    public function addDocumentoControllo($documento_controllo) {
        $documento_controllo->setControlloProgetto($this);
        $this->documenti_controllo->add($documento_controllo);
        return $this;
    }

    public function getProcedura(): Procedura {
        return $this->getRichiesta()->getProcedura();
    }

    public function getDescrizioneEsito(): ?string {
        if (is_null($this->esito)) {
            return "-";
        }

        return $this->esito->getDescrizione();
    }

    public function setDataInizioControlli(?\DateTime $dataInizioControlli): self {
        $this->data_inizio_controlli = $dataInizioControlli;

        return $this;
    }

    public function getDataInizioControlli(): ?\DateTime {
        return $this->data_inizio_controlli;
    }

    public function addDocumentiControllo(DocumentoControllo $documentiControllo): self {
        $this->documenti_controllo[] = $documentiControllo;

        return $this;
    }

    public function removeDocumentiControllo(DocumentoControllo $documentiControllo): void {
        $this->documenti_controllo->removeElement($documentiControllo);
    }

    public function addValutazioniChecklist(ValutazioneChecklistControllo $valutazioniChecklist): self {
        $this->valutazioni_checklist[] = $valutazioniChecklist;

        return $this;
    }

    public function removeValutazioniChecklist(ValutazioneChecklistControllo $valutazioniChecklist): void {
        $this->valutazioni_checklist->removeElement($valutazioniChecklist);
    }

    public function getRichiesta(): ?Richiesta {
        return $this->richiesta;
    }

    public function setRichiesta(Richiesta $richiesta) {
        $this->richiesta = $richiesta;
    }

    public function getValutazioniChecklistOrdinate() {
        $valori = $this->valutazioni_checklist->toArray();
        usort($valori, function ($a, $b) {
            return $a->getChecklist()->getOrdinamento() > $b->getChecklist()->getOrdinamento();
        });
        return $valori;
    }

    public function getNoteSopralluogo(): ?string {
        return $this->note_sopralluogo;
    }

    public function setNoteSopralluogo($note_sopralluogo) {
        $this->note_sopralluogo = $note_sopralluogo;
    }

    public function getNoteEsito() {
        return $this->note_esito;
    }

    public function setNoteEsito($note_esito) {
        $this->note_esito = $note_esito;
    }

    public function getDataValidazione() {
        return $this->data_validazione;
    }

    public function setDataValidazione($data_validazione) {
        $this->data_validazione = $data_validazione;
    }

    public function getControlloFaseDesk() {
        return $this->controllo_fase_desk;
    }

    public function getOsservazioniFaseDesk() {
        return $this->osservazioni_fase_desk;
    }

    public function getAcquisitaFaseSpr() {
        return $this->acquisita_fase_spr;
    }

    public function getRichiestaFaseSpr() {
        return $this->richiesta_fase_spr;
    }

    public function getTipoSedeFaseSpr() {
        return $this->tipo_sede_fase_spr;
    }

    public function getConclusioniFaseSpr() {
        return $this->conclusioni_fase_spr;
    }

    public function getOsservazioniBenFaseSpr() {
        return $this->osservazioni_ben_fase_spr;
    }

    public function getSpeseAmmesse() {
        return $this->spese_ammesse;
    }

    public function getSpeseRivalutazione() {
        return $this->spese_rivalutazione;
    }

    public function getSpeseNonAmmissibili() {
        return $this->spese_non_ammissibili;
    }

    public function setControlloFaseDesk($controllo_fase_desk) {
        $this->controllo_fase_desk = $controllo_fase_desk;
    }

    public function setOsservazioniFaseDesk($osservazioni_fase_desk) {
        $this->osservazioni_fase_desk = $osservazioni_fase_desk;
    }

    public function setAcquisitaFaseSpr($acquisita_fase_spr) {
        $this->acquisita_fase_spr = $acquisita_fase_spr;
    }

    public function setRichiestaFaseSpr($richiesta_fase_spr) {
        $this->richiesta_fase_spr = $richiesta_fase_spr;
    }

    public function setTipoSedeFaseSpr($tipo_sede_fase_spr) {
        $this->tipo_sede_fase_spr = $tipo_sede_fase_spr;
    }

    public function setConclusioniFaseSpr($conclusioni_fase_spr) {
        $this->conclusioni_fase_spr = $conclusioni_fase_spr;
    }

    public function setOsservazioniBenFaseSpr($osservazioni_ben_fase_spr) {
        $this->osservazioni_ben_fase_spr = $osservazioni_ben_fase_spr;
    }

    public function setSpeseAmmesse($spese_ammesse) {
        $this->spese_ammesse = $spese_ammesse;
    }

    public function setSpeseRivalutazione($spese_rivalutazione) {
        $this->spese_rivalutazione = $spese_rivalutazione;
    }

    public function setSpeseNonAmmissibili($spese_non_ammissibili) {
        $this->spese_non_ammissibili = $spese_non_ammissibili;
    }

    public function getIndirizzo(): ?Indirizzo {
        return $this->indirizzo;
    }

    public function setIndirizzo(Indirizzo $indirizzo) {
        $this->indirizzo = $indirizzo;
    }

    public function getDataControllo(): ?\DateTime {
        return $this->data_controllo;
    }

    public function setDataControllo(\DateTime $data_controllo = null) {
        $this->data_controllo = $data_controllo;
    }

    function getCampione(): ControlloCampione {
        return $this->campione;
    }

    function getTipologia(): string {
        return $this->tipologia;
    }

    function setCampione(ControlloCampione $campione): void {
        $this->campione = $campione;
    }

    function setTipologia(string $tipologia): void {
        $this->tipologia = $tipologia;
    }

    //attibuto di supporto per campionamento stabilità
    protected $selezionato;
    
    function getSelezionato() {
        return $this->selezionato;
    }

    function setSelezionato($selezionato): void {
        $this->selezionato = $selezionato;
    }

}
