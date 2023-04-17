<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_in01")
 */
class VistaIN01 {
    use StrutturaRichiestaTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\Length(max=60, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @var string
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="TC44_45IndicatoriOutput")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $indicatore;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\GreaterThan(value=0, groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $val_programmato;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     */
    protected $valore_realizzato;

    public function setCodLocaleProgetto(string $codLocaleProgetto): self {
        $this->cod_locale_progetto = $codLocaleProgetto;

        return $this;
    }

    public function getCodLocaleProgetto(): ?string {
        return $this->cod_locale_progetto;
    }

    public function setValProgrammato(string $importo_pulito): self {
        $this->val_programmato = $importo_pulito;

        return $this;
    }

    public function getValProgrammato(): ?string {
        return $this->val_programmato;
    }

    public function setValoreRealizzato(string $importo_pulito): self {
        $this->valore_realizzato = $importo_pulito;

        return $this;
    }

    public function getValoreRealizzato(): ?string {
        return $this->valore_realizzato;
    }

    public function setIndicatore(TC44_45IndicatoriOutput $indicatore): self {
        $this->indicatore = $indicatore;

        return $this;
    }

    public function getIndicatore(): ?TC44_45IndicatoriOutput {
        return $this->indicatore;
    }
}
