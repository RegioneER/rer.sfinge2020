<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\Richiesta;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_ap05")
 */
class VistaAP05 {
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
     * @ORM\ManyToOne(targetEntity="TC15StrumentoAttuativo")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     *
     * @var TC15StrumentoAttuativo
     */
    protected $struttura_attuativa;

    public function setCodLocaleProgetto(?string $codLocaleProgetto): self {
        $this->cod_locale_progetto = $codLocaleProgetto;

        return $this;
    }

    public function getCodLocaleProgetto(): ?string {
        return $this->cod_locale_progetto;
    }

    public function setStrutturaAttuativa(?TC15StrumentoAttuativo $strutturaAttuativa): self {
        $this->struttura_attuativa = $strutturaAttuativa;

        return $this;
    }

    public function getStrutturaAttuativa(): ?TC15StrumentoAttuativo {
        return $this->struttura_attuativa;
    }

    public function setRichiesta(?Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }
}
