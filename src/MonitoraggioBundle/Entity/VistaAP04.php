<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_ap04")
 */
class VistaAP04 {
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
     * @ORM\ManyToOne(targetEntity="TC14SpecificaStato")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $tc14_specifica_stato;

    /**
     * @ORM\Column(type="string", length=1, nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max="1", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="(1|2)", match=true, message="Valore flag cancellazione non valido", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $stato;

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

    public function getTc14SpecificaStato(): ?TC14SpecificaStato {
        return $this->tc14_specifica_stato;
    }

    public function setTc14SpecificaStato(?TC14SpecificaStato $tc14_specifica_stato): self {
        $this->tc14_specifica_stato = $tc14_specifica_stato;
        return $this;
    }

    public function getStato(): ?string {
        return $this->stato;
    }

    public function setStato(?string $stato): self {
        $this->stato = $stato;
        return $this;
    }
}
