<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_pr00")
 */
class VistaPR00 {
    use StrutturaRichiestaTrait;
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\Length(max=60, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="TC46FaseProcedurale")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc46_fase_procedurale;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_inizio_prevista;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $data_inizio_effettiva;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_fine_prevista;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $data_fine_effettiva;

    public function setCodLocaleProgetto(string $codLocaleProgetto): self {
        $this->cod_locale_progetto = $codLocaleProgetto;

        return $this;
    }

    public function getCodLocaleProgetto(): ?string {
        return $this->cod_locale_progetto;
    }

    public function setDataInizioPrevista(?\DateTime $dataInizioPrevista): self {
        $this->data_inizio_prevista = $dataInizioPrevista;

        return $this;
    }

    public function getDataInizioPrevista(): ?\DateTime {
        return $this->data_inizio_prevista;
    }

    public function setDataInizioEffettiva(?\DateTime $dataInizioEffettiva): self {
        $this->data_inizio_effettiva = $dataInizioEffettiva;

        return $this;
    }

    public function getDataInizioEffettiva(): ?\DateTime {
        return $this->data_inizio_effettiva;
    }

    public function setDataFinePrevista(?\DateTime $dataFinePrevista): self {
        $this->data_fine_prevista = $dataFinePrevista;

        return $this;
    }

    public function getDataFinePrevista(): ?\DateTime {
        return $this->data_fine_prevista;
    }

    public function setDataFineEffettiva(?\DateTime $dataFineEffettiva): self {
        $this->data_fine_effettiva = $dataFineEffettiva;

        return $this;
    }

    public function getDataFineEffettiva(): ?\DateTime {
        return $this->data_fine_effettiva;
    }

    public function setTc46FaseProcedurale(?TC46FaseProcedurale $tc46FaseProcedurale): self {
        $this->tc46_fase_procedurale = $tc46FaseProcedurale;

        return $this;
    }

    public function getTc46FaseProcedurale(): ?TC46FaseProcedurale {
        return $this->tc46_fase_procedurale;
    }
}
