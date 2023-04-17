<?php

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use RichiesteBundle\Entity\Richiesta;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\RichiestaPianoCostiRepository")
 * @ORM\Table(name="richieste_piano_costi")
 */
class RichiestaPianoCosti extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="mon_piano_costi")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $richiesta;

    /**
     * @ORM\Column(type="bigint", nullable=false)
     * @Assert\Regex(pattern="/^\d{4}$/", match=true, message="sfinge.monitoraggio.invalidYear")
     */
    protected $anno_piano;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\Regex(pattern="/^\d+\.?\d*$/", match=true, message="sfinge.monitoraggio.invalidNumber")
     * @Assert\GreaterThan(value=0, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo_da_realizzare;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\Regex(pattern="/^\d+\.?\d*$/", match=true, message="sfinge.monitoraggio.invalidNumber")
     * @Assert\GreaterThan(value=0, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo_realizzato;

    public function __construct(?Richiesta $richiesta = null) {
        $this->richiesta = $richiesta;
    }

    public function setAnnoPiano(int $annoPiano): self {
        $this->anno_piano = $annoPiano;

        return $this;
    }

    public function getAnnoPiano(): ?int {
        return $this->anno_piano;
    }

    public function setImportoDaRealizzare(float $importoDaRealizzare): self {
        $this->importo_da_realizzare = $importoDaRealizzare;

        return $this;
    }

    /**
     * @return string
     */
    public function getImportoDaRealizzare() {
        return $this->importo_da_realizzare;
    }

    public function setImportoRealizzato(float $importoRealizzato) {
        $this->importo_realizzato = $importoRealizzato;

        return $this;
    }

    public function getImportoRealizzato(): ?float {
        return $this->importo_realizzato;
    }

    public function setRichiesta(Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }

    public function getRichiesta(): Richiesta {
        return $this->richiesta;
    }
}
