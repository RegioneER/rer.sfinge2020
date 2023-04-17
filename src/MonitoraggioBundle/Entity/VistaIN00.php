<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_in00")
 */
class VistaIN00 {
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
     * @ORM\ManyToOne(targetEntity="TC42_43IndicatoriRisultato")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @var TC42_43IndicatoriRisultato
     */
    protected $indicatore;

    public function setCodLocaleProgetto(string $codLocaleProgetto): self {
        $this->cod_locale_progetto = $codLocaleProgetto;

        return $this;
    }

    public function getCodLocaleProgetto(): ?string {
        return $this->cod_locale_progetto;
    }

    public function setIndicatoreId(TC42_43IndicatoriRisultato $indicatore): self {
        $this->indicatore = $indicatore;

        return $this;
    }

    public function getIndicatoreId(): ?TC42_43IndicatoriRisultato {
        return $this->indicatore;
    }
}
