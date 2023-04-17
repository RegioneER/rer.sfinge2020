<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\TC38CausaleDisimpegno;
use Doctrine\Common\Collections\Collection;
use SoggettoBundle\Entity\Soggetto;
use AttuazioneControlloBundle\Entity\Revoche\Revoca;

/**
 * @author vbuscemi
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\RichiestaImpegniRepository")
 * @ORM\Table(name="richieste_impegni")
 */
class RichiestaImpegni extends EntityLoggabileCancellabile {
    const IMPEGNO = 'I';
    const DISIMPEGNO = 'D';

    public static $TIPOLOGIE_IMPEGNI_AMMESSI = [
        self::IMPEGNO => "Impegno",
        self::DISIMPEGNO => "Disimpegno",
        "I-TR" => "Impegno per trasferimento",
        "D-TR" => "Disimpegno per trasferimento", ];

    public static $TIPOLOGIE_IMPEGNI_TRASFERIMENTO = [
        "I-TR" => "Impegno per trasferimento",
        "D-TR" => "Disimpegno per trasferimento",
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="mon_impegni")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max="5", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "impegni_beneficiario"})
     * @Assert\NotNull(groups={"Default", "impegni_beneficiario"})
     * @Assert\Choice(
     *      choices={"I", "D", "I-TR", "D-TR"}, 
     *      groups={"Default", "impegni_beneficiario"},
     *      message="sfinge.monitoraggio.invalidValue"
     * )
     * @var string
     */
    protected $tipologia_impegno;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\NotNull(groups={"Default", "impegni_beneficiario"})
     * @Assert\Date(groups={"Default", "impegni_beneficiario"})
     * @Assert\LessThanOrEqual(
     *      value="today", 
     *      message="sfinge.monitoraggio.lessThanEqualToday", 
     *      groups={"Default", "impegni_beneficiario"}
     * )
     * @var \DateTime
     */
    protected $data_impegno;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "impegni_beneficiario"})
     * @Assert\GreaterThan(value=0, message="sfinge.monitoraggio.greaterThan", groups={"Default", "impegni_beneficiario"})
     * @var string|float
     */
    protected $importo_impegno;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC38CausaleDisimpegno")
     * @ORM\JoinColumn(name="causale_disimpegno_id", referencedColumnName="id", nullable=true)
     * @var TC38CausaleDisimpegno
     */
    protected $tc38_causale_disimpegno;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(
     *      max=1000, 
     *      groups={"Default", "impegni_beneficiario"}, 
     *      maxMessage="Il campo non può superare i {{ limit }} caratteri"
     * )
     * @var string
     */
    protected $note_impegno;

    /**
     * @ORM\OneToMany(targetEntity="ImpegniAmmessi", mappedBy="richiesta_impegni", cascade={"persist", "remove"})
     * @var Collection|ImpegniAmmessi[]
     */
    protected $mon_impegni_ammessi;

    /**
     * @ORM\OneToMany(targetEntity="DocumentoImpegno", mappedBy="impegno", cascade={"persist", "remove"})
     * @var Collection|DocumentoImpegno[]
     */
    protected $documenti;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @var string|null
     */
    protected $codice;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\Revoche\Revoca", inversedBy="impegno")
     * @ORM\JoinColumn(nullable=true)
     * @var Revoca|null
     */
    protected $revoca;

    public function __construct(?Richiesta $richiesta = null, ?string $tipologia = self::IMPEGNO) {
        $this->mon_impegni_ammessi = new ArrayCollection();
        $this->documenti = new ArrayCollection();
        $this->richiesta = $richiesta;
        $this->tipologia_impegno = $tipologia;

        if ($richiesta) {
            $this->codice = $this->calcolaCodice();
        }
    }

    public function getDescrizioneTipologiaImpegno(): ?string {
        return self::$TIPOLOGIE_IMPEGNI_AMMESSI[$this->tipologia_impegno];
    }

    public function getSoggetto(): Soggetto {
        return $this->richiesta->getSoggetto();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setTipologiaImpegno(string $tipologiaImpegno): self {
        $this->tipologia_impegno = $tipologiaImpegno;

        return $this;
    }

    public function getTipologiaImpegno(): ?string {
        return $this->tipologia_impegno;
    }

    public function setDataImpegno(?\DateTime $dataImpegno): self {
        $this->data_impegno = $dataImpegno;

        return $this;
    }

    public function getDataImpegno(): ?\DateTime {
        return $this->data_impegno;
    }

    /**
     * @param string|float $importoImpegno
     */
    public function setImportoImpegno($importoImpegno): self {
        $this->importo_impegno = $importoImpegno;

        return $this;
    }

    /**
     * @return string|float
     */
    public function getImportoImpegno() {
        return $this->importo_impegno;
    }

    public function setNoteImpegno(?string $noteImpegno): self {
        $this->note_impegno = $noteImpegno;

        return $this;
    }

    public function getNoteImpegno(): ?string {
        return $this->note_impegno;
    }

    public function setRichiesta(Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }

    public function getRichiesta(): ?Richiesta {
        return $this->richiesta;
    }

    public function setTc38CausaleDisimpegno(?TC38CausaleDisimpegno $tc38CausaleDisimpegno = null): self {
        $this->tc38_causale_disimpegno = $tc38CausaleDisimpegno;

        return $this;
    }

    public function getTc38CausaleDisimpegno(): ?TC38CausaleDisimpegno {
        return $this->tc38_causale_disimpegno;
    }

    public function addMonImpegniAmmessi(ImpegniAmmessi $monImpegniAmmessi): self {
        $this->mon_impegni_ammessi[] = $monImpegniAmmessi;

        return $this;
    }

    public function removeMonImpegniAmmessi(ImpegniAmmessi $monImpegniAmmessi): void {
        $this->mon_impegni_ammessi->removeElement($monImpegniAmmessi);
    }

    /**
     * @return Collection|ImpegniAmmessi[]
     */
    public function getMonImpegniAmmessi(): Collection {
        return $this->mon_impegni_ammessi;
    }

    /**
     * @Assert\IsTrue(message="sfinge.monitoraggio.invalidValue", groups={"Default", "impegni_beneficiario"})
     */
    public function isCausaleDisimpegnoValid(): bool {
        return \in_array($this->tipologia_impegno, ['I', 'I-TR']) ||
            !\is_null($this->tc38_causale_disimpegno);
    }

    public function addDocumenti(DocumentoImpegno $documenti): self {
        $this->documenti[] = $documenti;

        return $this;
    }

    public function removeDocumenti(DocumentoImpegno $documenti): void {
        $this->documenti->removeElement($documenti);
    }

    /**
     * @return Collection|DocumentoImpegno[]
     */
    public function getDocumenti(): Collection {
        return $this->documenti;
    }

    public function setCodice(?string $codice): self {
        $this->codice = $codice;

        return $this;
    }

    public function getCodice(): ?string {
        return $this->codice;
    }

    public function calcolaCodice(): string {
        $progressivo = $this->richiesta->getMonImpegni()->count() + 1;
        $protocollo = $this->richiesta->getProtocollo();

        return "{$protocollo}_{$this->tipologia_impegno}_{$progressivo}";
    }

    public function setRevoca(?Revoca $revoca): self {
        $this->revoca = $revoca;

        return $this;
    }

    public function getRevoca(): ?Revoca {
        return $this->revoca;
    }
}
