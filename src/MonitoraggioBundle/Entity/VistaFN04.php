<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_fn04")
 */
class VistaFN04 {
    use StrutturaRichiestaTrait;
    use HasCodLocaleProgetto;

    /**
     * @ORM\ManyToOne(targetEntity="TC38CausaleDisimpegno")
     * @ORM\JoinColumn(nullable=true)
     * @var TC38CausaleDisimpegno|null
     */
    protected $tc38_causale_disimpegno;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\Length(max=20, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     * @var string
     */
    protected $cod_impegno;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="/^(I|D|I-TR|D-TR)$/", message="sfinge.monitoraggio.invalidValue", match=true, groups={"Default", "esportazione_monitoraggio"})
     * @var string
     */
    protected $tipologia_impegno;

    /**
     * @ORM\Id
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @var \DateTime
     */
    protected $data_impegno;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo_impegno;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     * @var string|null
     */
    protected $note_impegno;

    public function setCodImpegno(string $codImpegno): self {
        $this->cod_impegno = $codImpegno;

        return $this;
    }

    public function getCodImpegno(): ?string {
        return $this->cod_impegno;
    }

    public function setTipologiaImpegno(?string $tipologiaImpegno): self {
        $this->tipologia_impegno = $tipologiaImpegno;

        return $this;
    }

    public function getTipologiaImpegno(): ?string {
        return $this->tipologia_impegno;
    }

    public function setDataImpegno(\DateTime $dataImpegno): self {
        $this->data_impegno = $dataImpegno;

        return $this;
    }

    public function getDataImpegno(): ?\DateTime {
        return $this->data_impegno;
    }

    public function setImportoImpegno($importoImpegno): self {
        $importo_pulito = str_replace(',', '.', $importoImpegno);
        $this->importo_impegno = (float) $importo_pulito;

        return $this;
    }

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

    public function setTc38CausaleDisimpegno(?TC38CausaleDisimpegno $tc38CausaleDisimpegno): self {
        $this->tc38_causale_disimpegno = $tc38CausaleDisimpegno;

        return $this;
    }

    public function getTc38CausaleDisimpegno(): ?TC38CausaleDisimpegno {
        return $this->tc38_causale_disimpegno;
    }
}
