<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use BaseBundle\Annotation as Sfinge;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use AnagraficheBundle\Entity\Persona;
use SoggettoBundle\Entity\Soggetto;
use DocumentoBundle\Entity\DocumentoFile;
use RichiesteBundle\Entity\Richiesta;
use ProtocollazioneBundle\Entity\RichiestaProtocolloProroga;

/**
 * @ORM\Table(name="proroghe")
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\ProrogheRepository")
 */
class Proroga extends EntityLoggabileCancellabile {
    const PROROGA_AVVIO = 'PROROGA_AVVIO';
    const PROROGA_FINE = 'PROROGA_FINE';
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta", inversedBy="proroghe")
     * @ORM\JoinColumn(nullable=false)
     * @var AttuazioneControlloRichiesta
     */
    protected $attuazione_controllo_richiesta;

    /**
     * @ORM\ManyToOne(targetEntity="AnagraficheBundle\Entity\Persona")
     * @ORM\JoinColumn(nullable=true)
     * @var Persona|null
     */
    private $firmatario;

    /**
     * @ORM\ManyToOne(targetEntity="StatoProroga")
     * @ORM\JoinColumn(nullable=true)
     * @Sfinge\CampoStato
     * @var StatoProroga|null
     */
    protected $stato;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
     * @ORM\JoinColumn(nullable=true)
     * @var DocumentoFile|null
     */
    protected $documento_proroga;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
     * @ORM\JoinColumn(nullable=true)
     * @var DocumentoFile|null
     */
    protected $documento_proroga_firmato;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime|null
     */
    protected $data_proroga;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime|null
     */
    protected $data_avvio_progetto;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime|null
     */
    protected $data_fine_progetto;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime|null
     */
    protected $data_avvio_approvata;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime|null
     */
    protected $data_fine_approvata;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $motivazioni;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $nota_pa;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime|null
     */
    protected $data_approvazione;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool|null
     */
    protected $approvata;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool|null
     */
    protected $gestita;

    /**
     * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloProroga", mappedBy="proroga")
     * @Assert\Valid
     * @var Collection|RichiestaProtocolloProroga[]
     */
    protected $richieste_protocollo;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime|null
     */
    protected $data_invio;

    /**
     * @ORM\Column(name="tipo_proroga", type="string", length=45, nullable=true)
     * @var string|null
     */
    protected $tipo_proroga;

    /**
     * @ORM\OneToMany(targetEntity="DocumentoProroga", mappedBy="proroga", cascade={"persist", "remove"})
     * @var Collection|DocumentoProroga[]
     */
    protected $documenti;

    public function getId() {
        return $this->id;
    }

    public function setFirmatario(Persona $firmatario = null) {
        $this->firmatario = $firmatario;

        return $this;
    }

    public function getFirmatario(): ?Persona {
        return $this->firmatario;
    }

    public function setStato(StatoProroga $stato = null): self {
        $this->stato = $stato;

        return $this;
    }

    public function getStato(): ?StatoProroga {
        return $this->stato;
    }

    public function getDataProroga(): ?\DateTime {
        return $this->data_proroga;
    }

    public function getMotivazioni(): ?string {
        return $this->motivazioni;
    }

    public function setDataProroga(?\DateTime $data_proroga): self {
        $this->data_proroga = $data_proroga;
        return $this;
    }

    public function setMotivazioni(?string $motivazioni): self {
        $this->motivazioni = $motivazioni;
        return $this;
    }

    public function getSoggetto(): ?Soggetto {
        return $this->getRichiesta()->getSoggetto();
    }

    public function getDocumentoProroga(): ?DocumentoFile {
        return $this->documento_proroga;
    }

    public function getDocumentoProrogaFirmato(): ?DocumentoFile {
        return $this->documento_proroga_firmato;
    }

    public function setDocumentoProroga(?DocumentoFile $documento_proroga): self {
        $this->documento_proroga = $documento_proroga;
        return $this;
    }

    public function setDocumentoProrogaFirmato(?DocumentoFile $documento_proroga_firmato): self {
        $this->documento_proroga_firmato = $documento_proroga_firmato;
        return $this;
    }

    public function getAttuazioneControlloRichiesta(): ?AttuazioneControlloRichiesta {
        return $this->attuazione_controllo_richiesta;
    }

    public function setAttuazioneControlloRichiesta(AttuazioneControlloRichiesta $attuazione_controllo_richiesta): self {
        $this->attuazione_controllo_richiesta = $attuazione_controllo_richiesta;
        return $this;
    }

    public function getRichiesta(): ?Richiesta {
        return $this->getAttuazioneControlloRichiesta()->getRichiesta();
    }

    /**
     * @var Collection|RichiestaProtocolloProroga[]
     */
    public function getRichiesteProtocollo(): Collection {
        return $this->richieste_protocollo;
    }

    public function setRichiesteProtocollo(Collection $richieste_protocollo): self {
        $this->richieste_protocollo = $richieste_protocollo;
        return $this;
    }

    public function getDataInvio(): ?\DateTime {
        return $this->data_invio;
    }

    public function setDataInvio(?\DateTime $data_invio): self {
        $this->data_invio = $data_invio;
        return $this;
    }

    public function isRichiestaDisabilitata(): bool {
        $stato = $this->getStato()->getCodice();
        return StatoProroga::PROROGA_INSERITA != $stato;
    }

    public function getNomeClasse(): string {
        return "Proroga";
    }

    public function getDataAvvioProgetto(): ?\DateTime {
        return $this->data_avvio_progetto;
    }

    public function setDataAvvioProgetto(?\DateTime $data_avvio_progetto): self {
        $this->data_avvio_progetto = $data_avvio_progetto;

        return $this;
    }

    public function getDataFineProgetto(): ?\DateTime {
        return $this->data_fine_progetto;
    }

    public function setDataFineProgetto(?\DateTime $data_fine_progetto): self {
        $this->data_fine_progetto = $data_fine_progetto;
        return $this;
    }

    public function getTipoProroga(): ?string {
        return $this->tipo_proroga;
    }

    public function setTipoProroga(?string $tipo_proroga): self {
        $this->tipo_proroga = $tipo_proroga;
        return $this;
    }

    public function getNotaPa(): ?string {
        return $this->nota_pa;
    }

    public function getDataApprovazione(): ?\DateTime {
        return $this->data_approvazione;
    }

    public function setNotaPa(?string $nota_pa): self {
        $this->nota_pa = $nota_pa;
        return $this;
    }

    public function setDataApprovazione(?\DateTime $data_approvazione): self {
        $this->data_approvazione = $data_approvazione;
        return $this;
    }

    public function getApprovata(): ?bool {
        return $this->approvata;
    }

    public function setApprovata(?bool $approvata): self {
        $this->approvata = $approvata;
        return $this;
    }

    public function getGestita(): ?bool {
        return $this->gestita;
    }

    public function setGestita(?bool $gestita): self {
        $this->gestita = $gestita;
        return $this;
    }

    public function getDataAvvioApprovata(): ?\DateTime {
        return $this->data_avvio_approvata;
    }

    public function getDataFineApprovata(): ?\DateTime {
        return $this->data_fine_approvata;
    }

    public function setDataAvvioApprovata(?\DateTime $data_avvio_approvata): self {
        $this->data_avvio_approvata = $data_avvio_approvata;
        return $this;
    }

    public function setDataFineApprovata(?\DateTime $data_fine_approvata): self {
        $this->data_fine_approvata = $data_fine_approvata;
        return $this;
    }

    public function getProtocollo(): string {
        $richiestaProtocollo = null;
        // in caso di più richieste protocollo mi prendo l'ultima(la più recente)
        foreach ($this->richieste_protocollo as $r) {
            if ('ProtocolloProroga' == $r->getNomeClasse()) {
                $richiestaProtocollo = $r;
            }
        }

        $protocollo = '-';
        if (!is_null($richiestaProtocollo)) {
            $protocollo = $richiestaProtocollo->getProtocollo();
        }

        return $protocollo;
    }

    public function __construct() {
        $this->richieste_protocollo = new ArrayCollection();
        $this->documenti = new ArrayCollection();
    }

    public function addRichiesteProtocollo(RichiestaProtocolloProroga $richiesteProtocollo): self {
        $this->richieste_protocollo[] = $richiesteProtocollo;

        return $this;
    }

    public function removeRichiesteProtocollo(RichiestaProtocolloProroga $richiesteProtocollo): void {
        $this->richieste_protocollo->removeElement($richiesteProtocollo);
    }

    public function addDocumenti(DocumentoProroga $documenti): self {
        $this->documenti[] = $documenti;

        return $this;
    }

    public function removeDocumenti(DocumentoProroga $documenti): void {
        $this->documenti->removeElement($documenti);
    }

    /**
     * @return Collection|DocumentoProroga[]
     */
    public function getDocumenti(): Collection {
        return $this->documenti;
    }

    public function isStatoFinale(): bool {
        return \in_array($this->getStato()->getCodice(), [StatoProroga::PROROGA_INVIATA_PA, StatoProroga::PROROGA_PROTOCOLLATA]);
    }

    public function __toString() {
        return $this->getProtocollo() ?: $this->id ?? '-';
    }
    
    public function isApprovata() {
        return $this->approvata == true;
    }
    
    public function getEsitoString() {
        if($this->getGestita() == true) {
            return $this->isApprovata() == true ? 'Approvata' : 'Non approvata';
        }else {
            return 'Da gestire';
        }
    }
}
