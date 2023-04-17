<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DocumentoBundle\Entity\DocumentoFile;
use MonitoraggioBundle\Entity\TC44_45IndicatoriOutput;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="RichiesteBundle\Repository\IndicatoreOutputRepository")
 * @ORM\Table(name="indicatori_output",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"indicatore_id", "richiesta_id", "data_cancellazione"})
 *     })
 */
class IndicatoreOutput extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Richiesta
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="mon_indicatore_output")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     */
    protected $richiesta;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC44_45IndicatoriOutput")
     * @ORM\JoinColumn(name="indicatore_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     *
     * @var TC44_45IndicatoriOutput
     */
    protected $indicatore;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\GreaterThanOrEqual(value=0)
     * @Assert\Regex("/^\d+(\.|,)?\d*$/", groups={"rendicontazione_beneficiario", "Default"})
     * @Assert\NotNull(groups={"presentazione_beneficiario"})
     *
     * @var string
     */
    protected $val_programmato;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\GreaterThanOrEqual(value=0, groups={"rendicontazione_beneficiario", "Default"})
     * @Assert\NotNull(groups={"rendicontazione_beneficiario"})
     * @Assert\Regex("/^\d+(\.|,)?\d*$/", groups={"rendicontazione_beneficiario", "Default"})
     *
     * @var string
     */
    protected $valore_realizzato;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\GreaterThanOrEqual(value=0, groups={"rendicontazione_istruttoria", "Default"})
     * @Assert\NotNull(groups={"rendicontazione_istruttoria"})
     * @Assert\Regex("/^\d+(\.|,)?\d*$/", groups={"rendicontazione_istruttoria", "Default"})
     */
    protected $valore_validato;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\GreaterThanOrEqual(value=0, groups={ "Default"})
     * @Assert\Regex("/^\d+(\.|,)?\d*$/", groups={ "Default"})
     */
    protected $valore_monitoraggio;

    /**
     * @ORM\OneToMany(targetEntity="DocumentoIndicatoreOutput", mappedBy="indicatore", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="documenti_id", referencedColumnName="id")
     * @var Collection|DocumentoIndicatoreOutput[]
     */
    protected $documenti;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $validoA;

    public function __construct(Richiesta $richiesta = null, ?TC44_45IndicatoriOutput $indicatore = null) {
        $this->richiesta = $richiesta;
        $this->indicatore = $indicatore;
        $this->documenti = new ArrayCollection();
    }

    /**
     * @Assert\IsTrue(groups={"rendicontazione_beneficiario"})
     */
    public function isDocumentiValid(): bool {
        return !$this->indicatore->getDocumentazioneObbligatoria() || $this->documenti->count() > 0 || 0 == $this->valore_realizzato;
    }

    /**
     * @return int
     */
    public function getId(): ?int {
        return $this->id;
    }

    public function setValProgrammato(?string $valProgrammato): self {
        $this->val_programmato = $valProgrammato;

        return $this;
    }

    public function getValProgrammato(): ?string {
        return $this->val_programmato;
    }

    public function setValoreRealizzato(?string $valoreRealizzato): self {
        $this->valore_realizzato = $valoreRealizzato;

        return $this;
    }

    public function getValoreRealizzato(): ?string {
        return $this->valore_realizzato;
    }

    public function setRichiesta(Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }

    public function getRichiesta(): ?Richiesta {
        return $this->richiesta;
    }

    public function setIndicatore(?TC44_45IndicatoriOutput $indicatore = null): self {
        $this->indicatore = $indicatore;

        return $this;
    }

    public function getIndicatore(): ?TC44_45IndicatoriOutput {
        return $this->indicatore;
    }

    public function isAutomatico(): bool {
        return $this->indicatore->isAutomatico();
    }

    public function addDocumenti(DocumentoFile $documento): self {
        $associazione = new DocumentoIndicatoreOutput($this, $documento);
        $this->documenti[] = $associazione;

        return $this;
    }

    public function removeDocumenti(DocumentoFile $documento): DocumentoIndicatoreOutput {
        $daEliminare = $this->documenti->filter(function (DocumentoIndicatoreOutput $associazione) use ($documento) {
            return $associazione->getDocumento() == $documento;
        })->first();
        if (false === $daEliminare) {
            throw new \Exception('Nessun documento da eliminare');
        }
        $this->documenti->removeElement($daEliminare);
        return $daEliminare;
    }

    public function getDocumenti(): Collection {
        return $this->documenti->map(function (DocumentoIndicatoreOutput $associazione) {
            return $associazione->getDocumento();
        });
    }

    public function setDocumenti(Collection $documenti): self {
        $indicatore = $this;
        $this->documenti = $documenti->map(function (DocumentoFile $doc) use ($indicatore) {
            return new DocumentoIndicatoreOutput($indicatore, $doc);
        });
        return $this;
    }

    public function setValoreValidato(?string $valoreValidato): self {
        $this->valore_validato = $valoreValidato;

        return $this;
    }

    public function getValoreValidato(): ?string {
        return $this->valore_validato;
    }

    public function getValoreMonitoraggio(): ?string {
        return $this->valore_monitoraggio;
    }

    public function setValoreMonitoraggio(?string $valore): self {
        $this->valore_monitoraggio = $valore;

        return $this;
    }

    public function setValidoA(?\DateTime $date): self {
        $this->validoA = $date;

        return $this;
    }

    public function getValidoA(): ?\DateTime {
        return $this->validoA;
    }
}
