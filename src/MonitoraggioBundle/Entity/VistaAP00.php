<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_ap00")
 */
class VistaAP00 {
    use StrutturaRichiestaTrait;
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="string", length=500, nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Length(max="500", maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     * @var string
     */
    protected $titolo_progetto;

    /**
     * @ORM\Column(type="string", length=1300, nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Length(max="1300", maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $sintesi_prg;

    /**
     * @ORM\ManyToOne(targetEntity="TC5TipoOperazione")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @var TC5TipoOperazione
     */
    protected $tc5_tipo_operazione;

    /**
     * @ORM\ManyToOne(targetEntity="TC6TipoAiuto")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @var TC6TipoAiuto
     */
    protected $tc6_tipo_aiuto;

    /**
     * @ORM\ManyToOne(targetEntity="TC48TipoProceduraAttivazioneOriginaria")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     * @var TC48TipoProceduraAttivazioneOriginaria
     */
    protected $tc48_tipo_procedura_attivazione_originaria;

    /**
     * @ORM\Column(type="string", length=15, nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Length(max="15", maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     * @var string
     */
    protected $cup;

    /**
     * @ORM\Column(type="date", nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     * @var \DateTime|null
     */
    protected $data_inizio;

    /**
     * @ORM\Column(type="date", nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     * @var \DateTime|null
     */
    protected $data_fine_prevista;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     * @var \DateTime|null
     */
    protected $data_fine_effettiva;

    public function getTc5TipoOperazione(): ?TC5TipoOperazione {
        return $this->tc5_tipo_operazione;
    }

    public function setTc5TipoOperazione(TC5TipoOperazione $tc5_tipo_operazione): self {
        $this->tc5_tipo_operazione = $tc5_tipo_operazione;
        return $this;
    }

    public function getTc6TipoAiuto(): ?TC6TipoAiuto {
        return $this->tc6_tipo_aiuto;
    }

    public function setTc6TipoAiuto(TC6TipoAiuto $tc6_tipo_aiuto): self {
        $this->tc6_tipo_aiuto = $tc6_tipo_aiuto;
        return $this;
    }

    public function getTc48TipoProceduraAttivazioneOriginaria(): ?TC48TipoProceduraAttivazioneOriginaria {
        return $this->tc48_tipo_procedura_attivazione_originaria;
    }

    public function setTc48TipoProceduraAttivazioneOriginaria(TC48TipoProceduraAttivazioneOriginaria $tc48_tipo_procedura_attivazione_originaria): self {
        $this->tc48_tipo_procedura_attivazione_originaria = $tc48_tipo_procedura_attivazione_originaria;
        return $this;
    }

    public function getCodLocaleProgetto(): ?string {
        return $this->cod_locale_progetto;
    }

    public function setCodLocaleProgetto(string $cod_locale_progetto): self {
        $this->cod_locale_progetto = $cod_locale_progetto;
        return $this;
    }

    public function getTitoloProgetto(): ?string {
        return $this->titolo_progetto;
    }

    public function setTitoloProgetto(string $titolo_progetto): self {
        $this->titolo_progetto = $titolo_progetto;
        return $this;
    }

    public function getSintesiPrg(): ?string {
        return $this->sintesi_prg;
    }

    public function setSintesiPrg(string $sintesi_prg): self {
        $this->sintesi_prg = $sintesi_prg;
        return $this;
    }

    public function getCup(): ?string {
        return $this->cup;
    }

    public function setCup(string $cup): self {
        $this->cup = $cup;
        return $this;
    }

    public function getDataInizio(): ?\DateTime {
        return $this->data_inizio;
    }

    public function setDataInizio(\DateTime $data_inizio): self {
        $this->data_inizio = $data_inizio;
        return $this;
    }

    public function getDataFinePrevista(): ?\DateTime {
        return $this->data_fine_prevista;
    }

    public function setDataFinePrevista(\DateTime $data_fine_prevista): self {
        $this->data_fine_prevista = $data_fine_prevista;
        return $this;
    }

    public function getDataFineEffettiva(): ?\DateTime {
        return $this->data_fine_effettiva;
    }

    public function setDataFineEffettiva(?\DateTime $data_fine_effettiva): self {
        $this->data_fine_effettiva = $data_fine_effettiva;
        return $this;
    }
}
