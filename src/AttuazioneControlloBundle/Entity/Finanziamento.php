<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\Richiesta;
use GeoBundle\Entity\GeoComune;
use AnagraficheBundle\Entity\Persona;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;
use MonitoraggioBundle\Entity\TC34DeliberaCIPE;
use MonitoraggioBundle\Entity\TC35Norma;
use SoggettoBundle\Entity\Soggetto;
use MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\FinanziamentoRepository")
 * @ORM\Table(name="finanziamenti")
 */
class Finanziamento extends EntityLoggabileCancellabile {
    const FINANZIAMENTO_STATO = 0.35;
    const FINANZIAMENTO_UE = 0.5;

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var string
     */
    protected $id;

    /**
     * @var Richiesta
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", cascade={"persist"}, inversedBy="mon_finanziamenti")
     * @ORM\JoinColumn(nullable=false, name="richiesta_id")
     * @Assert\NotNull
     */
    protected $richiesta;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC33FonteFinanziaria")
     * @ORM\JoinColumn(name="fondo_id", referencedColumnName="id", nullable=true)
     * @var TC33FonteFinanziaria|null
     */
    protected $tc33_fonte_finanziaria;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC35Norma")
     * @ORM\JoinColumn(name="norma_id", referencedColumnName="id", nullable=true)
     * @var TC35Norma|null
     */
    protected $tc35_norma;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC34DeliberaCIPE")
     * @ORM\JoinColumn(name="delibera_cipe_id", referencedColumnName="id", nullable=true)
     * @var TC34DeliberaCIPE|null
     */
    protected $tc34_delibera_cipe;

    /**
     * @ORM\ManyToOne(targetEntity="GeoBundle\Entity\GeoComune")
     * @ORM\JoinColumn(name="localizzazione_geografica_id", referencedColumnName="id", nullable=true)
     * @var GeoComune|null
     */
    protected $localizzazione_geografica;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica")
     * @ORM\JoinColumn(nullable=true)
     * @var TC16LocalizzazioneGeografica|null
     */
    protected $tc16_localizzazione_geografica;

    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Soggetto")
     * @ORM\JoinColumn(name="cofinanziatore_id", referencedColumnName="id", nullable=true)
     * @var Soggetto|null
     */
    protected $cofinanziatore;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     */
    protected $importo;

    public function __construct(Richiesta $richiesta = null) {
        $this->richiesta = $richiesta;
    }

    public function getId() {
        return $this->id;
    }

    public function getRichiesta(): ?Richiesta {
        return $this->richiesta;
    }

    public function getTc33FonteFinanziaria(): ?TC33FonteFinanziaria {
        return $this->tc33_fonte_finanziaria;
    }

    public function getTc35Norma(): ?TC35Norma {
        return $this->tc35_norma;
    }

    public function getTc34DeliberaCipe(): ?TC34DeliberaCIPE {
        return $this->tc34_delibera_cipe;
    }

    public function getLocalizzazioneGeografica(): ?GeoComune {
        return $this->localizzazione_geografica;
    }

    public function getCofinanziatore(): ?Soggetto {
        return $this->cofinanziatore;
    }

    public function getImporto() {
        return $this->importo;
    }

    public function setId($id): self {
        $this->id = $id;

        return $this;
    }

    public function setRichiesta(Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }

    public function setTc33FonteFinanziaria(?TC33FonteFinanziaria $tc33_fonte_finanziaria): self {
        $this->tc33_fonte_finanziaria = $tc33_fonte_finanziaria;

        return $this;
    }

    public function setTc35Norma(?TC35Norma $tc35_norma): self {
        $this->tc35_norma = $tc35_norma;

        return $this;
    }

    public function setTc34DeliberaCipe(?TC34DeliberaCIPE $tc34_delibera_cipe): self {
        $this->tc34_delibera_cipe = $tc34_delibera_cipe;

        return $this;
    }

    public function setLocalizzazioneGeografica(?GeoComune $localizzazione_geografica): self {
        $this->localizzazione_geografica = $localizzazione_geografica;

        return $this;
    }

    public function setCofinanziatore(?Soggetto $cofinanziatore): self {
        $this->cofinanziatore = $cofinanziatore;

        return $this;
    }

    public function setImporto($importo): self {
        $this->importo = $importo;

        return $this;
    }

    public function setTc16LocalizzazioneGeografica(?TC16LocalizzazioneGeografica $tc16LocalizzazioneGeografica): self
    {
        $this->tc16_localizzazione_geografica = $tc16LocalizzazioneGeografica;

        return $this;
    }

    public function getTc16LocalizzazioneGeografica(): ?TC16LocalizzazioneGeografica
    {
        return $this->tc16_localizzazione_geografica;
    }
}
