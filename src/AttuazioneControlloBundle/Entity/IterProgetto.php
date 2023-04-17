<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\TC46FaseProcedurale;

/**
 * @ORM\Entity
 * @ORM\table(name="iter_progetto",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"richiesta_id", "fase_procedurale_id", "data_cancellazione"})
 *     })
 */
class IterProgetto extends EntityLoggabileCancellabile {
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Richiesta
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="mon_iter_progetti")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     */
    protected $richiesta;

    /**
     * @var TC46FaseProcedurale
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC46FaseProcedurale", inversedBy="iter")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     */
    protected $fase_procedurale;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true, type="date")
     * @Assert\NotNull(groups={"presentazione_richiesta"})
     * @Assert\Date
     */
    protected $data_inizio_prevista;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true, type="date")
     * @Assert\Date(groups={"rendicontazione_beneficiario", "Default"})
     * @Assert\NotNull(groups={"rendicontazione_iter_progetto_beneficiario_finale"})
     */
    protected $data_inizio_effettiva;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true, type="date")
     * @Assert\NotNull(groups={"presentazione_richiesta"})
     * @Assert\Date
     */
    protected $data_fine_prevista;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true, type="date")
     * @Assert\Date(groups={"rendicontazione_beneficiario", "Default"})
     * @Assert\NotNull(groups={"rendicontazione_iter_progetto_beneficiario_finale"})
     */
    protected $data_fine_effettiva;

    /**
     * @param Richiesta $richiesta
     */
    public function __construct(Richiesta $richiesta = null, TC46FaseProcedurale $fase = null) {
        $this->richiesta = $richiesta;
        $this->fase_procedurale = $fase;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    public function setDataInizioPrevista(?\DateTime $dataInizioPrevista): self {
        $this->data_inizio_prevista = $dataInizioPrevista;

        return $this;
    }

    public function getDataInizioPrevista(): ?\DateTime {
        return $this->data_inizio_prevista;
    }

    public function setDataInizioEffettiva(?\DateTime $dataInizioEffettiva): self {
        $this->data_inizio_effettiva = $dataInizioEffettiva;

        return $this;
    }

    public function getDataInizioEffettiva(): ?\DateTime {
        return $this->data_inizio_effettiva;
    }

    public function setDataFinePrevista(?\DateTime $dataFinePrevista): self {
        $this->data_fine_prevista = $dataFinePrevista;

        return $this;
    }

    public function getDataFinePrevista(): ?\DateTime {
        return $this->data_fine_prevista;
    }

    public function setDataFineEffettiva(?\DateTime $dataFineEffettiva): self {
        $this->data_fine_effettiva = $dataFineEffettiva;

        return $this;
    }

    public function getDataFineEffettiva(): ?\DateTime {
        return $this->data_fine_effettiva;
    }

    public function setRichiesta(Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }

    public function getRichiesta(): Richiesta {
        return $this->richiesta;
    }

    public function setFaseProcedurale(TC46FaseProcedurale $faseProcedurale): self {
        $this->fase_procedurale = $faseProcedurale;

        return $this;
    }

    public function getFaseProcedurale(): ?TC46FaseProcedurale {
        return $this->fase_procedurale;
    }

    /**
     * @Assert\IsTrue(message="La data fine effettiva deve essere maggiore della data inizio effettiva", groups={"rendicontazione_iter_progetto_beneficiario_finale", "Default"})
     */
    public function isDataFineEffettivaValid(): bool {
        return
            \is_null($this->data_inizio_effettiva) ||
            \is_null($this->data_fine_effettiva) ||
            $this->data_fine_effettiva >= $this->data_inizio_effettiva;
    }

    /**
     * @Assert\IsTrue(message="La data fine prevista deve essere maggiore della data inizio prevista", groups={"rendicontazione_iter_progetto_beneficiario_finale", "Default"})
     */
    public function isDataFinePrevistaValid(): bool {
        return  \is_null($this->data_inizio_prevista) || 
                \is_null($this->data_fine_prevista) ||
                $this->data_fine_prevista >= $this->data_inizio_prevista;
    }

    public function setTutteLeDate(\DateTime $data = null): self {
        $this->data_fine_effettiva = $data;
        $this->data_fine_prevista = $data;
        $this->data_inizio_effettiva = $data;
        $this->data_inizio_prevista = $data;

        return $this;
    }
}
