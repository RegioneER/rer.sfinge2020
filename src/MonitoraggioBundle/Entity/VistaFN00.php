<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_fn00")
 */
class VistaFN00 {
    use StrutturaRichiestaTrait;
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @var string
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="TC33FonteFinanziaria")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @var TC33FonteFinanziaria
     */
    protected $tc33_fonte_finanziaria;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="TC35Norma")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @var TC35Norma
     */
    protected $tc35_norma;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="TC34DeliberaCIPE")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @var TC34DeliberaCIPE
     */
    protected $tc34_delibera_cipe;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="TC16LocalizzazioneGeografica")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @var TC16LocalizzazioneGeografica
     */
    protected $tc16_localizzazione_geografica;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=16, nullable=false)
     * @Assert\Length(max="16", maxMessage="sfinge.monitoraggio.maxLength")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max=16, min=11, maxMessage="sfinge.monitoraggio.maxLength", minMessage="sfinge.monitoraggio.minLength")
     * @var string
     */
    protected $cf_cofinanz;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     * @var string
     */
    protected $importo;

    public function setCodLocaleProgetto(string $codLocaleProgetto): self {
        $this->cod_locale_progetto = $codLocaleProgetto;

        return $this;
    }

    public function getCodLocaleProgetto(): ?string {
        return $this->cod_locale_progetto;
    }

    public function setCfCofinanz(string $cfCofinanz): self {
        $this->cf_cofinanz = $cfCofinanz;

        return $this;
    }

    public function getCfCofinanz(): ?string {
        return $this->cf_cofinanz;
    }

    public function setImporto(string $importo): self {
        $importo_pulito = str_replace(',', '.', $importo);
        $this->importo = (float) $importo_pulito;

        return $this;
    }

    public function getImporto(): ?string {
        return $this->importo;
    }

    public function setTc33FonteFinanziaria(TC33FonteFinanziaria $tc33FonteFinanziaria): self {
        $this->tc33_fonte_finanziaria = $tc33FonteFinanziaria;

        return $this;
    }

    public function getTc33FonteFinanziaria(): ?TC33FonteFinanziaria {
        return $this->tc33_fonte_finanziaria;
    }

    public function setTc35Norma(TC35Norma $tc35Norma): self {
        $this->tc35_norma = $tc35Norma;

        return $this;
    }

    public function getTc35Norma(): ?TC35Norma {
        return $this->tc35_norma;
    }

    public function setTc34DeliberaCipe(TC34DeliberaCIPE $tc34DeliberaCipe): self {
        $this->tc34_delibera_cipe = $tc34DeliberaCipe;

        return $this;
    }

    public function getTc34DeliberaCipe(): ?TC34DeliberaCIPE {
        return $this->tc34_delibera_cipe;
    }

    public function setTc16LocalizzazioneGeografica(TC16LocalizzazioneGeografica $tc16LocalizzazioneGeografica): self {
        $this->tc16_localizzazione_geografica = $tc16LocalizzazioneGeografica;

        return $this;
    }

    public function getTc16LocalizzazioneGeografica(): ?TC16LocalizzazioneGeografica {
        return $this->tc16_localizzazione_geografica;
    }
}
