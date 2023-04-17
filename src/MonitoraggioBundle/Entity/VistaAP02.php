<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_ap02")
 */
class VistaAP02 {
    use StrutturaRichiestaTrait;
    /**
     * @var TC7ProgettoComplesso|null
     * @ORM\ManyToOne(targetEntity="TC7ProgettoComplesso")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    protected $tc7_progetto_complesso;

    /**
     * @ORM\ManyToOne(targetEntity="TC8GrandeProgetto")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     * @var TC8GrandeProgetto|null
     */
    protected $tc8_grande_progetto;

    /**
     * @var TC9TipoLivelloIstituzione|null
     * @ORM\ManyToOne(targetEntity="TC9TipoLivelloIstituzione")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     */
    protected $tc9_tipo_livello_istituzione;

    /**
     * @var TC10TipoLocalizzazione|null
     * @ORM\ManyToOne(targetEntity="TC10TipoLocalizzazione")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc10_tipo_localizzazione;

    /**
     * @var TC13GruppoVulnerabileProgetto|null
     * @ORM\ManyToOne(targetEntity="TC13GruppoVulnerabileProgetto")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc13_gruppo_vulnerabile_progetto;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @var string
     * @ORM\Column(type="string", length=1, nullable=false)
     * @Assert\Length(max="1", maxMessage="sfinge.monitoraggio.maxLength")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="(S|N)", match=true, message="Valore flag cancellazione non valido", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $generatore_entrate;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\Length(max="1", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="(S|N)", match=true, message="Valore flag cancellazione non valido", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $fondo_di_fondi;

    /**
     * @Assert\IsTrue(groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.fondoDiFondiObbligatorio")
     */
    public function isFondoDiFondiObbligatorioValid() {
        return is_null($this->tc9_tipo_livello_istituzione) ||
            !\in_array($this->tc9_tipo_livello_istituzione->getLivIstituzioneStrFin(), [2, 3]) ||
            !empty($this->fondo_di_fondi);
    }

    public function getTc7ProgettoComplesso(): ?TC7ProgettoComplesso {
        return $this->tc7_progetto_complesso;
    }

    public function setTc7ProgettoComplesso(TC7ProgettoComplesso $tc7_progetto_complesso): self {
        $this->tc7_progetto_complesso = $tc7_progetto_complesso;
        return $this;
    }

    public function getTc8GrandeProgetto(): ?TC8GrandeProgetto {
        return $this->tc8_grande_progetto;
    }

    public function setTc8GrandeProgetto(TC8GrandeProgetto $tc8_grande_progetto): self {
        $this->tc8_grande_progetto = $tc8_grande_progetto;
        return $this;
    }

    public function getTc9TipoLivelloIstituzione(): ?TC9TipoLivelloIstituzione {
        return $this->tc9_tipo_livello_istituzione;
    }

    public function setTc9TipoLivelloIstituzione(?TC9TipoLivelloIstituzione $tc9_tipo_livello_istituzione): self {
        $this->tc9_tipo_livello_istituzione = $tc9_tipo_livello_istituzione;
        return $this;
    }

    public function getTc10TipoLocalizzazione(): ?TC10TipoLocalizzazione {
        return $this->tc10_tipo_localizzazione;
    }

    public function setTc10TipoLocalizzazione(TC10TipoLocalizzazione $tc10_tipo_localizzazione): self {
        $this->tc10_tipo_localizzazione = $tc10_tipo_localizzazione;
        return $this;
    }

    public function getTc13GruppoVulnerabileProgetto(): ?TC13GruppoVulnerabileProgetto {
        return $this->tc13_gruppo_vulnerabile_progetto;
    }

    public function setTc13GruppoVulnerabileProgetto(TC13GruppoVulnerabileProgetto $tc13_gruppo_vulnerabile_progetto): self {
        $this->tc13_gruppo_vulnerabile_progetto = $tc13_gruppo_vulnerabile_progetto;
        return $this;
    }

    public function getCodLocaleProgetto(): ?string {
        return $this->cod_locale_progetto;
    }

    public function setCodLocaleProgetto(string $cod_locale_progetto): self {
        $this->cod_locale_progetto = $cod_locale_progetto;
        return $this;
    }

    public function getGeneratoreEntrate(): ?string {
        return $this->generatore_entrate;
    }

    public function setGeneratoreEntrate(string $generatore_entrate): self {
        $this->generatore_entrate = $generatore_entrate;
        return $this;
    }

    public function getFondoDiFondi(): ?string {
        return $this->fondo_di_fondi;
    }

    public function setFondoDiFondi(?string $fondo_di_fondi) {
        $this->fondo_di_fondi = $fondo_di_fondi;
        return $this;
    }
}
