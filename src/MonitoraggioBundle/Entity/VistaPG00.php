<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\Richiesta;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_pg00")
 */
class VistaPG00 {
    use StrutturaRichiestaTrait;
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull
     * @var string
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength")
     * @var string
     */
    protected $cod_proc_agg;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Length(max="10", maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $cig;

    /**
     * @ORM\ManyToOne(targetEntity="TC22MotivoAssenzaCIG")
     * @ORM\JoinColumn(nullable=true)
     * @var TC22MotivoAssenzaCIG|null
     */
    protected $tc22_motivo_assenza_cig;

    /**
     * @ORM\Column(type="string", length=1500, nullable=true)
     * @Assert\Length(max="1500", maxMessage="sfinge.monitoraggio.maxLength")
     */
    protected $descr_procedura_agg;

    /**
     * @ORM\ManyToOne(targetEntity="TC23TipoProceduraAggiudicazione")
     * @ORM\JoinColumn(nullable=true)
     * @var TC23TipoProceduraAggiudicazione|null
     */
    protected $tc23_tipo_procedura_aggiudicazione;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @var string|null
     */
    protected $importo_procedura_agg;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date
     * @var \DateTime|null
     */
    protected $data_pubblicazione;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @var string|null
     */
    protected $importo_aggiudicato;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date
     * @var \DateTime|null
     */
    protected $data_aggiudicazione;

    public function setCodLocaleProgetto(string $codLocaleProgetto): self {
        $this->cod_locale_progetto = $codLocaleProgetto;

        return $this;
    }

    public function getCodLocaleProgetto(): string {
        return $this->cod_locale_progetto;
    }

    public function setCodProcAgg(string $codProcAgg): self {
        $this->cod_proc_agg = $codProcAgg;

        return $this;
    }

    public function getCodProcAgg(): ?string {
        return $this->cod_proc_agg;
    }

    public function setCig(?string $cig): self {
        $this->cig = $cig;

        return $this;
    }

    public function getCig(): ?string {
        return $this->cig;
    }

    public function setDescrProceduraAgg(?string $descrProceduraAgg): self {
        $this->descr_procedura_agg = $descrProceduraAgg;

        return $this;
    }

    public function getDescrProceduraAgg(): ?string {
        return $this->descr_procedura_agg;
    }

    public function setImportoProceduraAgg(?string $importoProceduraAgg): self {
        $this->importo_procedura_agg = $importoProceduraAgg;

        return $this;
    }

    public function getImportoProceduraAgg(): ?string {
        return $this->importo_procedura_agg;
    }

    public function setDataPubblicazione(?\DateTime $dataPubblicazione): self {
        $this->data_pubblicazione = $dataPubblicazione;

        return $this;
    }

    public function getDataPubblicazione(): ?\DateTime {
        return $this->data_pubblicazione;
    }

    public function setImportoAggiudicato(?string $importoAggiudicato): self {
        $this->importo_aggiudicato = $importoAggiudicato;

        return $this;
    }

    public function getImportoAggiudicato(): ?string {
        return $this->importo_aggiudicato;
    }

    public function setDataAggiudicazione(? \DateTime $dataAggiudicazione): self {
        $this->data_aggiudicazione = $dataAggiudicazione;

        return $this;
    }

    public function getDataAggiudicazione(): ? \DateTime {
        return $this->data_aggiudicazione;
    }

    public function setTc22MotivoAssenzaCig(?TC22MotivoAssenzaCIG $tc22MotivoAssenzaCig): self {
        $this->tc22_motivo_assenza_cig = $tc22MotivoAssenzaCig;

        return $this;
    }

    public function getTc22MotivoAssenzaCig(): ?TC22MotivoAssenzaCIG {
        return $this->tc22_motivo_assenza_cig;
    }

    public function setTc23TipoProceduraAggiudicazione(?TC23TipoProceduraAggiudicazione $tc23TipoProceduraAggiudicazione): self {
        $this->tc23_tipo_procedura_aggiudicazione = $tc23TipoProceduraAggiudicazione;

        return $this;
    }

    public function getTc23TipoProceduraAggiudicazione(): ?TC23TipoProceduraAggiudicazione {
        return $this->tc23_tipo_procedura_aggiudicazione;
    }

    public function setRichiesta(Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }
}
