<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use BaseBundle\Annotation as Sfinge;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ProtocollazioneBundle\Entity\RichiestaProtocolloVariazione;
use DocumentoBundle\Entity\DocumentoFile;
use AnagraficheBundle\Entity\Persona;
use IstruttorieBundle\Entity\ComunicazioneProgetto;
use RichiesteBundle\Entity\Richiesta;
use SoggettoBundle\Entity\Soggetto;
use SfingeBundle\Entity\Procedura;

/**
 * @ORM\Table(name="variazioni_richieste")
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\VariazioneRichiestaRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({
 *     "PIANO_COSTI": "VariazionePianoCosti",
 *     "DATI_BANCARI": "VariazioneDatiBancari",
 *     "GENERICA": "VariazioneGenerica",
 *     "SEDE_OPERATIVA": "VariazioneSedeOperativa",
 *     "REFERENTE": "VariazioneReferente",
 *     "VARIAZIONE": "VariazioneRichiesta"
 * })
 */
class VariazioneRichiesta extends EntityLoggabileCancellabile {
    const BUDGET = 'Budget - piano costo';
    const TIPI_VARIAZIONI = [
        self::BUDGET => VariazionePianoCosti::class,
        'Dati bancari' => VariazioneDatiBancari::class,
        'Generica' => VariazioneGenerica::class,
        'UL / Sede progetto' => VariazioneSedeOperativa::class,
        "Referente / RUP" => VariazioneReferente::class,
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var AttuazioneControlloRichiesta
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta", inversedBy="variazioni")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $attuazione_controllo_richiesta;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
     * @ORM\JoinColumn(nullable=true)
     * @var DocumentoFile|null
     */
    protected $documento_variazione;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
     * @ORM\JoinColumn(nullable=true)
     * @var DocumentoFile|null
     */
    protected $documento_variazione_firmato;

    /**
     * @ORM\ManyToOne(targetEntity="AnagraficheBundle\Entity\Persona")
     * @ORM\JoinColumn(nullable=true)
     * @var Persona|null
     */
    protected $firmatario;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\StatoVariazione")
     * @ORM\JoinColumn(nullable=true)
     * @Sfinge\CampoStato
     * @var StatoVariazione|null
     */
    protected $stato;

    /**
     * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloVariazione", mappedBy="variazione")
     * @var Collection|\ProtocollazioneBundle\Entity\RichiestaProtocolloVariazione[]
     */
    protected $richieste_protocollo;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaVariazioneRichiesta", mappedBy="variazione_richiesta", cascade={"persist"})
     * @var IstruttoriaVariazioneRichiesta|null
     */
    protected $istruttoria_variazione;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank(groups={"dati_generali"})
     * @var string|null
     */
    protected $note;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $note_istruttore;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_invio;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\DocumentoVariazione", mappedBy="variazione")
     * @var Collection|DocumentoVariazione[]
     */
    protected $documenti_variazione;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool|null
     */
    protected $esito_istruttoria;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool|null
     */
    protected $ignora_variazione;

    /**
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\ComunicazioneProgetto", mappedBy="variazione")
     * @var Collection|ComunicazioneProgetto[]
     */
    protected $comunicazioni_progetto;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_validazione;

    public function __construct(AttuazioneControlloRichiesta $atc = null) {
        $this->documenti_variazione = new ArrayCollection();
        $this->comunicazioni_progetto = new ArrayCollection();
        $this->richieste_protocollo = new ArrayCollection();
        $this->attuazione_controllo_richiesta = $atc;
    }

    public function getId() {
        return $this->id;
    }

    public function getDocumentoVariazione(): ?DocumentoFile {
        return $this->documento_variazione;
    }

    public function getDocumentoVariazioneFirmato(): ?DocumentoFile {
        return $this->documento_variazione_firmato;
    }

    public function getFirmatario(): ?Persona {
        return $this->firmatario;
    }

    public function getStato(): ?StatoVariazione {
        return $this->stato;
    }

    public function getRichiesteProtocollo(): Collection {
        return $this->richieste_protocollo;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setDocumentoVariazione(?DocumentoFile $documento_variazione) {
        $this->documento_variazione = $documento_variazione;
    }

    public function setDocumentoVariazioneFirmato(?DocumentoFile $documento_variazione_firmato) {
        $this->documento_variazione_firmato = $documento_variazione_firmato;
    }

    public function setFirmatario(?Persona $firmatario) {
        $this->firmatario = $firmatario;
    }

    public function setStato(?StatoVariazione $stato): self {
        $this->stato = $stato;

        return $this;
    }

    public function setRichiesteProtocollo(Collection $richieste_protocollo): self {
        $this->richieste_protocollo = $richieste_protocollo;

        return $this;
    }

    public function getNote(): ?string {
        return $this->note;
    }

    public function getDataInvio(): ?\DateTime {
        return $this->data_invio;
    }

    public function setNote(?string $note) {
        $this->note = $note;
    }

    public function setDataInvio(?\DateTime $data_invio) {
        $this->data_invio = $data_invio;
    }

    public function getIstruttoriaVariazione(): ?IstruttoriaVariazioneRichiesta {
        return $this->istruttoria_variazione;
    }

    public function setIstruttoriaVariazione(?IstruttoriaVariazioneRichiesta $istruttoria_variazione): self {
        $this->istruttoria_variazione = $istruttoria_variazione;

        return $this;
    }

    public function getAttuazioneControlloRichiesta(): ?AttuazioneControlloRichiesta {
        return $this->attuazione_controllo_richiesta;
    }

    public function setAttuazioneControlloRichiesta(AttuazioneControlloRichiesta $attuazione_controllo_richiesta): self {
        $this->attuazione_controllo_richiesta = $attuazione_controllo_richiesta;
        $this->attuazione_controllo_richiesta->addVariazioni($this);

        return $this;
    }

    public function getRichiesta(): Richiesta {
        return $this->attuazione_controllo_richiesta->getRichiesta();
    }

    public function getSoggetto(): Soggetto {
        return $this->getRichiesta()->getSoggetto();
    }

    public function getProcedura(): Procedura {
        return $this->getRichiesta()->getProcedura();
    }

    public function addRichiesteProtocollo(RichiestaProtocolloVariazione $richiesteProtocollo): self {
        $this->richieste_protocollo[] = $richiesteProtocollo;

        return $this;
    }

    public function removeRichiesteProtocollo(RichiestaProtocolloVariazione $richiesteProtocollo): void {
        $this->richieste_protocollo->removeElement($richiesteProtocollo);
    }

    public function addDocumentiVariazione(DocumentoVariazione $documentiVariazione): self {
        $this->documenti_variazione[] = $documentiVariazione;

        return $this;
    }

    public function removeDocumentiVariazione(DocumentoVariazione $documentiVariazione): void {
        $this->documenti_variazione->removeElement($documentiVariazione);
    }

    /**
     * @return Collection|DocumentoVariazione[]
     */
    public function getDocumentiVariazione(): Collection {
        return $this->documenti_variazione;
    }

    public function getEsitoIstruttoria(): ?bool {
        return $this->esito_istruttoria;
    }

    public function setEsitoIstruttoria(?bool $esito_istruttoria): self {
        $this->esito_istruttoria = $esito_istruttoria;
        return $this;
    }

    public function isEliminabile(): bool {
        return !\in_array($this->getStato()->getCodice(), [StatoVariazione::VAR_INVIATA_PA, StatoVariazione::VAR_PROTOCOLLATA]);
    }

    public function isRichiestaDisabilitata(): bool {
        $stato = $this->getStato()->getCodice();
        $disabilitata = StatoVariazione::VAR_INSERITA != $stato;

        return $disabilitata;
    }

    public function getDescrizioneEsito(): string {
        if (is_null($this->esito_istruttoria)) {
            return "-";
        }

        return $this->esito_istruttoria ? "Accettata" : "Respinta";
    }

    public function getNomeClasse(): string {
        return "VariazioneRichiesta";
    }

    public function getIgnoraVariazione(): ?bool {
        return $this->ignora_variazione;
    }

    public function setIgnoraVariazione(?bool $ignora_variazione): self {
        $this->ignora_variazione = $ignora_variazione;

        return $this;
    }

    public function getNoteIstruttore(): ?string {
        return $this->note_istruttore;
    }

    public function setNoteIstruttore(?string $note_istruttore): self {
        $this->note_istruttore = $note_istruttore;

        return $this;
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

    public function getProtocollo(): string {
        $richiestaProtocollo = null;
        // in caso di più richieste protocollo mi prendo l'ultima(la più recente)
        foreach ($this->richieste_protocollo as $r) {
            if ('ProtocolloVariazione' == $r->getNomeClasse()) {
                $richiestaProtocollo = $r;
            }
        }

        $protocollo = '-';
        if (!is_null($richiestaProtocollo)) {
            $protocollo = $richiestaProtocollo->getProtocollo();
        }

        return $protocollo;
    }

    /**
     * @return Collection|ComunicazioneProgetto[]
     */
    public function getComunicazioniProgetto(): Collection {
        return $this->comunicazioni_progetto;
    }

    public function setComunicazioniProgetto(Collection $comunicazioni_progetto): self {
        $this->comunicazioni_progetto = $comunicazioni_progetto;

        return $this;
    }

    public function isStatoFinale(): bool {
        return \in_array($this->getStato()->getCodice(), [StatoVariazione::VAR_INVIATA_PA, StatoVariazione::VAR_PROTOCOLLATA]);
    }

    public function getVariazioneRichiesta(): self {
        return $this;
    }

    public function isEsitata(\DateTime $dataRiferimento = null): bool {
        $dataRiferimento = $dataRiferimento ?? new \DateTime();

        $esitata = $this->esito_istruttoria &&
        !$this->ignora_variazione &&
        $dataRiferimento > $this->data_invio;

        return $esitata;
    }

    public function addComunicazioniProgetto(ComunicazioneProgetto $comunicazioniProgetto): self {
        $this->comunicazioni_progetto[] = $comunicazioniProgetto;

        return $this;
    }

    public function removeComunicazioniProgetto(ComunicazioneProgetto $comunicazioniProgetto): void {
        $this->comunicazioni_progetto->removeElement($comunicazioniProgetto);
    }

    public function getTipo(): string {
        $reflObj = new \ReflectionObject($this);
        $className = $reflObj->getName();
        $key = \array_search($className, self::TIPI_VARIAZIONI);

        return $key;
    }

    public function setDataValidazione(?\DateTime $dataValidazione): self {
        $this->data_validazione = $dataValidazione;

        return $this;
    }

    public function getDataValidazione(): ?\DateTime {
        return $this->data_validazione;
    }

    public function isPendente(): bool {
        return $this->isStatoFinale() && \is_null($this->getEsitoIstruttoria());
    }
    
    public function getTipiVariazioni() :array {
        $tipiVariazioni = self::TIPI_VARIAZIONI;
        /* Per il bando IRAP è prevista solamente la variazione di tipologia GENERICA.
            Ho preferito fare l’unset di tutti tranne uno piuttosto di creare l’array
            con un unico item perché in questo modo se dovesse cambiare la classe associata
            la modifica deve essere fatta solamente nella dichiarazione dell’array
        */
        $bandiIrap = [118, 125];
        $bandiMontagna = [171];
        if (in_array($this->getProcedura()->getId(), $bandiIrap)) {
            foreach ($tipiVariazioni as $key => $tipoVariazione) {
                if ($key != 'Generica') {
                    unset($tipiVariazioni[$key]);
                }
            }
        } elseif (in_array($this->getProcedura()->getId(), $bandiMontagna)) {
            foreach ($tipiVariazioni as $key => $tipoVariazione) {
                if ($key != 'Generica' && $key != 'Dati bancari') {
                    unset($tipiVariazioni[$key]);
                }
            }
        }
        return $tipiVariazioni;
    }
    
    public function getEsitoString() {
        if(!is_null($this->esito_istruttoria)) {
            return $this->isAmmessa() == true ? 'Ammessa' : 'Non ammessa';
        }else {
            return 'Da gestire';
        }
    }
    
    public function isAmmessa() {
        return $this->esito_istruttoria == true;
    }
}
