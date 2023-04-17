<?php

namespace SoggettoBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Indirizzo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use RichiesteBundle\Entity\SedeOperativa;

/**
 * @ORM\Entity(repositoryClass="SoggettoBundle\Entity\SedeRepository")
 * @ORM\Table(name="sedi")
 */
class Sede extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=1000)
     * @var string|null
     */
    private $denominazione;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $numero_rea;

    /**
     * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\Indirizzo", cascade={"persist"})
     * @ORM\JoinColumn(name="indirizzo_id", referencedColumnName="id", nullable=false)
     * @Assert\Valid
     * @var Indirizzo|null
     */
    private $indirizzo;

    /**
     * @ORM\ManyToOne(targetEntity="Soggetto", inversedBy="sedi")
     * @ORM\JoinColumn(name="soggetto_id", referencedColumnName="id", nullable=false)
     * @var Soggetto|null
     */
    private $soggetto;

    /**
     * @ORM\ManyToOne(targetEntity="Ateco", inversedBy="sede")
     * @ORM\JoinColumn(name="ateco_id", referencedColumnName="id")
     * @var Ateco|null
     */
    private $ateco;

    /**
     * @ORM\ManyToOne(targetEntity="Ateco")
     * @ORM\JoinColumn(name="ateco_secondario_id", referencedColumnName="id", nullable=true))
     */
    private $ateco_secondario;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\SedeOperativa", mappedBy="sede")
     * @var Collection|SedeOperativa[]
     */
    protected $sedeOperativa;
    protected $disabilitaCombo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_cessazione;

    public function __construct(Soggetto $soggetto = null, Indirizzo $indirizzo = null) {
        $this->soggetto = $soggetto;
        $this->indirizzo = $indirizzo;
        $this->sedeOperativa = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getDenominazione(): ?string {
        return $this->denominazione;
    }

    public function getNumeroRea(): ?string {
        return $this->numero_rea;
    }

    public function getIndirizzo(): ?Indirizzo {
        return $this->indirizzo;
    }

    public function getSoggetto(): ?Soggetto {
        return $this->soggetto;
    }

    public function getAteco(): ?Ateco {
        return $this->ateco;
    }

    public function setDenominazione(?string $denominazione): self {
        $this->denominazione = $denominazione;

        return $this;
    }

    public function setNumeroRea(?string $numero_rea): self {
        $this->numero_rea = $numero_rea;

        return $this;
    }

    public function setIndirizzo(Indirizzo $indirizzo): self {
        $this->indirizzo = $indirizzo;

        return $this;
    }

    public function setSoggetto(Soggetto $soggetto): self {
        $this->soggetto = $soggetto;

        return $this;
    }

    public function setAteco(?Ateco $ateco): self {
        $this->ateco = $ateco;

        return $this;
    }

    public function getDisabilitaCombo() {
        return $this->disabilitaCombo;
    }

    public function setDisabilitaCombo($disabilitaCombo) {
        $this->disabilitaCombo = $disabilitaCombo;
    }

    public function getAtecoSecondario(): ?Ateco {
        return $this->ateco_secondario;
    }

    public function setAtecoSecondario(?Ateco $ateco_secondario) {
        $this->ateco_secondario = $ateco_secondario;
    }

    public static function SedeFromSoggetto(Soggetto $soggetto): self {
        $sede = new Sede();
        $sede->denominazione = $soggetto->getDenominazione();
        $sede->ateco = $soggetto->getCodiceAteco();
        $sede->soggetto = $soggetto;

        $indirizzo = Indirizzo::IndirizzoFromSoggetto($soggetto);

        $sede->indirizzo = $indirizzo;
        return $sede;
    }

    public function setDataCessazione(?\DateTime $dataCessazione): self {
        $this->data_cessazione = $dataCessazione;

        return $this;
    }

    public function getDataCessazione(): ?\DateTime {
        return $this->data_cessazione;
    }

    public function addSedeOperativa(SedeOperativa $sedeOperativa): self {
        $this->sedeOperativa[] = $sedeOperativa;

        return $this;
    }

    public function removeSedeOperativa(SedeOperativa $sedeOperativa): void {
        $this->sedeOperativa->removeElement($sedeOperativa);
    }

    public function getSedeOperativa(): Collection {
        return $this->sedeOperativa;
    }

    public function isAttiva(): bool {
        return \is_null($this->data_cessazione);
    }

    public function isCessata(): bool {
        return !$this->isAttiva();
    }

    public function __toString() {
        return $this->denominazione ?? '';
    }

}
