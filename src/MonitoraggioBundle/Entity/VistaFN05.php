<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_fn05")
 */
class VistaFN05 {
    use StrutturaRichiestaTrait;
    use HasCodLocaleProgetto;

    /**
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @ORM\JoinColumn
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc4_programma;

    /**
     * @ORM\ManyToOne(targetEntity="TC36LivelloGerarchico")
     * @ORM\JoinColumn
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc36_livello_gerarchico;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\Length(max=20, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_impegno;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="/^(I|D|I-TR|D-TR)$/", message="sfinge.monitoraggio.invalidValue", match=true, groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tipologia_impegno;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $data_impegno;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $data_imp_amm;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="/^(I|D|I-TR|D-TR)$/", message="sfinge.monitoraggio.invalidValue", match=true, groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tipologia_imp_amm;

    /**
     * @ORM\ManyToOne(targetEntity="TC38CausaleDisimpegno")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $tc38_causale_disimpegno_amm;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $importo_imp_amm;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $note_imp;

    public function setCodImpegno(string $codImpegno): self {
        $this->cod_impegno = $codImpegno;

        return $this;
    }

    public function getCodImpegno(): ?string {
        return $this->cod_impegno;
    }

    public function setTipologiaImpegno(string $tipologiaImpegno): self {
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

    public function setDataImpAmm(\DateTime $dataImpAmm): self {
        $this->data_imp_amm = $dataImpAmm;

        return $this;
    }

    public function getDataImpAmm(): ?\DateTime {
        return $this->data_imp_amm;
    }

    public function setTipologiaImpAmm(string $tipologiaImpAmm): self {
        $this->tipologia_imp_amm = $tipologiaImpAmm;

        return $this;
    }

    public function getTipologiaImpAmm(): ?string {
        return $this->tipologia_imp_amm;
    }

    public function setImportoImpAmm($importoImpAmm): self {
        $importo_pulito = str_replace(',', '.', $importoImpAmm);
        $this->importo_imp_amm = (float) $importo_pulito;

        return $this;
    }

    public function getImportoImpAmm() {
        return $this->importo_imp_amm;
    }

    public function setNoteImp(?string $noteImp): self {
        $this->note_imp = $noteImp;

        return $this;
    }

    public function getNoteImp(): ?string {
        return $this->note_imp;
    }

    public function setTc4Programma(TC4Programma $tc4Programma): self {
        $this->tc4_programma = $tc4Programma;

        return $this;
    }

    public function getTc4Programma(): ?TC4Programma {
        return $this->tc4_programma;
    }

    public function setTc36LivelloGerarchico(TC36LivelloGerarchico $tc36LivelloGerarchico): self {
        $this->tc36_livello_gerarchico = $tc36LivelloGerarchico;

        return $this;
    }

    public function getTc36LivelloGerarchico(): ?TC36LivelloGerarchico {
        return $this->tc36_livello_gerarchico;
    }

    public function setTc38CausaleDisimpegnoAmm(?TC38CausaleDisimpegno $tc38CausaleDisimpegnoAmm): self {
        $this->tc38_causale_disimpegno_amm = $tc38CausaleDisimpegnoAmm;

        return $this;
    }

    public function getTc38CausaleDisimpegnoAmm(): ?TC38CausaleDisimpegno {
        return $this->tc38_causale_disimpegno_amm;
    }
}
