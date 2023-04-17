<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use RichiesteBundle\Entity\Richiesta;
use Doctrine\Common\Collections\Collection;
use SfingeBundle\Entity\Procedura;
use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Entity\TC14SpecificaStato;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity
 * @ORM\Table(name="richieste_programmi",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"richiesta_id", "programma_id", "data_cancellazione"})
 *     }))
 */
class RichiestaProgramma extends EntityLoggabileCancellabile {
    use Id;
    const STATO_ATTIVO = 1;
    const STATO_NON_ATTIVO = 2;

    /** @var array */
    protected static $STATI = [
        self::STATO_ATTIVO => 'Attivo',
        self::STATO_NON_ATTIVO => 'Non attivo',
    ];

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC4Programma", inversedBy="richieste_programmi")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     * @var TC4Programma
     */
    protected $tc4_programma;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC14SpecificaStato")
     * @ORM\JoinColumn(name="specifica_stato_id", referencedColumnName="id", nullable=true)
     * @var TC14SpecificaStato
     */
    protected $tc14_specifica_stato;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="mon_programmi")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @ORM\Column(type="string", length=1, nullable=false)
     * @Assert\Length(max="1", maxMessage="sfinge.monitoraggio.maxLength")
     * @Assert\NotNull
     * @var string
     */
    protected $stato;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\RichiestaProgrammaClassificazione", mappedBy="richiesta_programma", cascade={"persist", "remove"})
     */
    protected $classificazioni;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico", mappedBy="richiesta_programma", cascade={"persist", "remove"})
     *
     * @var Collection|RichiestaLivelloGerarchico[]
     */
    protected $mon_livelli_gerarchici;

    /**
     * @Assert\IsTrue(message="Se non attivo, è obbligatorio sepcificare lo specifica stato")
     */
    public function isTc14SpecificaStatoValid(): bool {
        return !is_null($this->tc14_specifica_stato) || 1 == $this->stato;
    }

    public function getTc4Programma(): ?TC4Programma {
        return $this->tc4_programma;
    }

    public function setTc4Programma(TC4Programma $tc4_programma): self {
        $this->tc4_programma = $tc4_programma;

        return $this;
    }

    public function getTc14SpecificaStato(): ?TC14SpecificaStato {
        return $this->tc14_specifica_stato;
    }

    public function setTc14SpecificaStato(?TC14SpecificaStato $tc14_specifica_stato): self {
        $this->tc14_specifica_stato = $tc14_specifica_stato;

        return $this;
    }

    public function getRichiesta(): ?Richiesta {
        return $this->richiesta;
    }

    public function setRichiesta(Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }

    public function getStato(): ?string {
        return $this->stato;
    }

    public function setStato(string $stato): self {
        $this->stato = $stato;

        return $this;
    }

    /**
     * @return RichiestaProgrammaClassificazione[]|Collection
     */
    public function getClassificazioni(): Collection {
        return $this->classificazioni;
    }

    /**
     * @param RichiestaProgrammaClassificazione[]|Collection $classificazioni
     * @return self
     */
    public function setClassificazioni(Collection $classificazioni): self {
        $this->classificazioni = $classificazioni;
        return $this;
    }

    public function getProcedura(): Procedura {
        return $this->getRichiesta()->getProcedura();
    }

    public static function getStati(): array {
        return self::$STATI;
    }

    public function __construct(Richiesta $richiesta = null, ?string $stato = null, ?TC4Programma $programma = null) {
        $this->richiesta = $richiesta;
        $this->stato = $stato;
        $this->tc4_programma = $programma;
        $this->mon_livelli_gerarchici = new ArrayCollection();
        $this->classificazioni = new ArrayCollection();
    }

    public function visualizzaStato(): string {
        return self::$STATI[$this->getStato()];
    }

    /**
     * @return RichiestaLivelloGerarchico[]|Collection
     */
    public function getMonLivelliGerarchici(): Collection {
        return $this->mon_livelli_gerarchici;
    }

    /**
     * @param RichiestaLivelloGerarchico[]|Collection $value
     */
    public function setMonLivelliGerarchici(Collection $value): self {
        return $this->mon_livelli_gerarchici = $value;
        return $this;
    }

    public function addMonLivelliGerarchici(RichiestaLivelloGerarchico $monLivelliGerarchici): self {
        $this->mon_livelli_gerarchici[] = $monLivelliGerarchici;

        return $this;
    }

    public function addClassificazioni(RichiestaProgrammaClassificazione $classificazioni): self {
        $this->classificazioni[] = $classificazioni;

        return $this;
    }

    public function removeClassificazioni(RichiestaProgrammaClassificazione $classificazioni): void {
        $this->classificazioni->removeElement($classificazioni);
    }

    public function removeMonLivelliGerarchici(RichiestaLivelloGerarchico $monLivelliGerarchici): void {
        $this->mon_livelli_gerarchici->removeElement($monLivelliGerarchici);
    }

    /**
     * 
     * @return Collection|RichiestaLivelloGerarchico[] 
     */
    public function getLivelliGerarchiciObiettivoSpecifico(): Collection {
        return $this->mon_livelli_gerarchici->filter(function (RichiestaLivelloGerarchico $lv): bool {
            return $lv->getTc36LivelloGerarchico()->getObiettiviSpecifici()->count() > 0;
        });
    }
}
