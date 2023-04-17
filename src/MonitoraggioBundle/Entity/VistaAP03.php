<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_ap03")
 */
class VistaAP03 {
    use StrutturaRichiestaTrait;
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     *
     * @var string
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     *
     * @var TC4Programma
     */
    protected $tc4_programma;

    /**
     * @ORM\ManyToOne(targetEntity="TC11TipoClassificazione")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     *
     * @var TC11TipoClassificazione
     */
    protected $tc11_tipo_classificazione;

    /**
     * @ORM\ManyToOne(targetEntity="TC12Classificazione")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     *
     * @var TC12Classificazione
     */
    protected $tc12_classificazione;

    public function setCodLocaleProgetto(?string $codLocaleProgetto): self {
        $this->cod_locale_progetto = $codLocaleProgetto;

        return $this;
    }

    public function getCodLocaleProgetto(): ?string {
        return $this->cod_locale_progetto;
    }

    public function setTc4Programma(?TC4Programma $tc4Programma): self {
        $this->tc4_programma = $tc4Programma;

        return $this;
    }

    public function getTc4Programma(): ?TC4Programma {
        return $this->tc4_programma;
    }

    public function setTc11TipoClassificazione(?TC11TipoClassificazione $tc11TipoClassificazione): self {
        $this->tc11_tipo_classificazione = $tc11TipoClassificazione;

        return $this;
    }

    public function getTc11TipoClassificazione(): ?TC11TipoClassificazione {
        return $this->tc11_tipo_classificazione;
    }

    public function setTc12Classificazione(?TC12Classificazione $tc12Classificazione): self {
        $this->tc12_classificazione = $tc12Classificazione;

        return $this;
    }

    public function getTc12Classificazione(): ?TC12Classificazione {
        return $this->tc12_classificazione;
    }
}
