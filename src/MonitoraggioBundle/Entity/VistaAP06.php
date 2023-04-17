<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_ap06")
 */
class VistaAP06 {
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
     * @var TC16LocalizzazioneGeografica
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="TC16LocalizzazioneGeografica")
     * @ORM\JoinColumn(name="tc16_localizzazione_geografica_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc16_localizzazione_geografica;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max="1000", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     * @var string
     */
    protected $indirizzo;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max="5", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     * @var string
     */
    protected $cod_cap;

    public function setCodLocaleProgetto(?string $codLocaleProgetto): self {
        $this->cod_locale_progetto = $codLocaleProgetto;

        return $this;
    }

    public function getCodLocaleProgetto(): ?string {
        return $this->cod_locale_progetto;
    }

    public function getTc16LocalizzazioneGeografica(): ?TC16LocalizzazioneGeografica {
        return $this->tc16_localizzazione_geografica;
    }

    public function setTc16LocalizzazioneGeografica(?TC16LocalizzazioneGeografica $localizzazioneGeografica): self {
        $this->tc16_localizzazione_geografica = $localizzazioneGeografica;
        return $this;
    }

    public function getIndirizzo(): ?string {
        return $this->indirizzo;
    }

    public function setIndirizzo(?string $indirizzo): self {
        $this->indirizzo = $indirizzo;
        return $this;
    }

    public function getCodCap(): ?string {
        return $this->cod_cap;
    }

    public function setCodCap(?string $cod_cap): self {
        $this->cod_cap = $cod_cap;
        return $this;
    }
}
