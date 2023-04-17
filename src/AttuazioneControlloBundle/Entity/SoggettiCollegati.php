<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\Richiesta;
use SoggettoBundle\Entity\Soggetto;
use MonitoraggioBundle\Entity\TC24RuoloSoggetto;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\SoggettiCollegatiRepository")
 * @ORM\Table(name="soggetti_collegati",
 * uniqueConstraints={
 *      @ORM\UniqueConstraint(columns={"richiesta_id", "soggetto_id", "ruolo_sog_id", "data_cancellazione"})
 * }))
 */
class SoggettiCollegati extends EntityLoggabileCancellabile {

    const COD_UNI_IPA_ER = 'r_emiro';
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC24RuoloSoggetto", cascade={"persist"})
     * @ORM\JoinColumn(name="ruolo_sog_id", nullable=true)
     * 
     * @var TC24RuoloSoggetto|null
     */
    protected $tc24_ruolo_soggetto;

    /**
     * @var Richiesta
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", cascade={"persist"}, inversedBy="mon_soggetti_correlati")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     * @var Richiesta|null
     */
    protected $richiesta;

    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Soggetto", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     * @var Soggetto|null
     */
    protected $soggetto;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $cod_uni_ipa;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @var string
     */
    protected $note;

    /**
     * @Assert\IsTrue(message="Codice UNI IPA non inserito")
     */
    public function isCodUniIpaValido(): bool {
        return !\is_null($this->cod_uni_ipa) ||
             !$this->soggetto ||
             !$this->soggetto->isSoggettoPubblico();
    }

    public function __construct(?Richiesta $richiesta = null, ?Soggetto $soggetto = null) {
        $this->richiesta = $richiesta;
        $this->soggetto = $soggetto;
    }

    public function getId() {
        return $this->id;
    }

    public function getTc24RuoloSoggetto(): ?TC24RuoloSoggetto {
        return $this->tc24_ruolo_soggetto;
    }

    public function getRichiesta(): ?Richiesta {
        return $this->richiesta;
    }

    public function getSoggetto(): ?Soggetto {
        return $this->soggetto;
    }

    public function getCodUniIpa(): ?string {
        return $this->cod_uni_ipa;
    }

    public function getNote(): ?string {
        return $this->note;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTc24RuoloSoggetto(TC24RuoloSoggetto $tc24_ruolo_soggetto): self {
        $this->tc24_ruolo_soggetto = $tc24_ruolo_soggetto;

        return $this;
    }

    public function setRichiesta(Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }

    public function setSoggetto(Soggetto $soggetto): self {
        $this->soggetto = $soggetto;

        return $this;
    }

    public function setCodUniIpa(string $cod_uni_ipa): self {
        $this->cod_uni_ipa = $cod_uni_ipa;

        return $this;
    }

    public function setNote(?string $note): self {
        $this->note = $note;

        return $this;
    }
}
