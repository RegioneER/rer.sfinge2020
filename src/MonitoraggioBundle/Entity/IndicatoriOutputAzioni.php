<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 11/10/17
 * Time: 17:30
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use SfingeBundle\Entity\Azione;
use SfingeBundle\Entity\Asse;
use Doctrine\Common\Collections\Collection;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\IndicatoriOutputAzioniRepository")
 * @ORM\Table(name="indicatori_output_azioni")
 */
class IndicatoriOutputAzioni extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC44_45IndicatoriOutput")
     * @ORM\JoinColumn(name="indicatore_output_id", referencedColumnName="id", nullable=true)
     * @var TC44_45IndicatoriOutput
     * @Assert\NotNull
     */
    protected $indicatoreOutput;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Azione", inversedBy="indicatori_output_azioni")
     * @ORM\JoinColumn(name="azione_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $azione;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Asse")
     * @ORM\JoinColumn(nullable=true)
     * @var Asse
     * @Assert\NotNull
     */
    protected $asse;

    /**
     * @ORM\OneToMany(targetEntity="RichiestaIndicatoreOutput", mappedBy="indicatore_output")
     */
    protected $richieste_indicatori_output;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $validoDa;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     * @Assert\Expression("value > this.getValidoDa() or !value",
     *     message="La data di fine validità non può essere precedente della data di inizio"
     * )
     */
    protected $validoA;

    public function __construct(TC44_45IndicatoriOutput $indicatore = null, Azione $azione = null) {
        $this->indicatoreOutput = $indicatore;
        $this->azione = $azione;
        $this->richieste_indicatori_output = new ArrayCollection();
    }

    public function setIndicatoreOutput(?TC44_45IndicatoriOutput $indicatoreOutput): self {
        $this->indicatoreOutput = $indicatoreOutput;

        return $this;
    }

    public function getIndicatoreOutput(): ?TC44_45IndicatoriOutput {
        return $this->indicatoreOutput;
    }

    public function setAzione(Azione $azione): self {
        $this->azione = $azione;

        return $this;
    }

    public function getAzione(): ?Azione {
        return $this->azione;
    }

    public function addRichiesteIndicatoriOutput(RichiestaIndicatoreOutput $richiesteIndicatoriOutput): self {
        $this->richieste_indicatori_output[] = $richiesteIndicatoriOutput;

        return $this;
    }

    public function removeRichiesteIndicatoriOutput(RichiestaIndicatoreOutput $richiesteIndicatoriOutput): void {
        $this->richieste_indicatori_output->removeElement($richiesteIndicatoriOutput);
    }

    /**
     * @return Collection | RichiestaIndicatoreOutput[]
     */
    public function getRichiesteIndicatoriOutput(): Collection {
        return $this->richieste_indicatori_output;
    }

    public function setAsse(?Asse $asse = null): self {
        $this->asse = $asse;

        return $this;
    }

    public function getAsse(): ?Asse {
        return $this->asse;
    }

    public function getValidoDa(): ?\DateTime {
        return $this->validoDa;
    }

    public function getValidoA(): ?\DateTime {
        return $this->validoA;
    }

    public function setValidoA(?\DateTime $date): self {
        $this->validoA = $date;
        return $this;
    }

    public function setValidoDa(?\DateTime $date): self {
        $this->validoDa = $date;
        return $this;
    }

    public function isInCorso(\DateTimeInterface $ref): bool {
        $da = \is_null($this->validoDa) || $ref >= $this->validoDa;
        $a = \is_null($this->validoA) || $ref <= $this->validoA;

        return $da && $a;
    }
}
