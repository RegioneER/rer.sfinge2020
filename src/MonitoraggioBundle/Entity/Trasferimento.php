<?php

namespace MonitoraggioBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use SoggettoBundle\Entity\Soggetto;
use SfingeBundle\Entity\Procedura;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TrasferimentoRepository")
 * @ORM\Table(name="trasferimenti")
 */
class Trasferimento extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $programma;

    /**
     * @ORM\ManyToOne(targetEntity="TC49CausaleTrasferimento")
     * @ORM\JoinColumn(name="causale_trasferimento_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $causale_trasferimento;

    /**
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength")
     */
    protected $cod_trasferimento;

    /**
     * @ORM\Column(type="date", nullable=false)
     * @Assert\NotNull
     * @Assert\Date
     */
    protected $data_trasferimento;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=false)
     * @Assert\NotNull
     * @Assert\Type(type="float")
     */
    protected $importo_trasferimento;

    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Soggetto")
     * @ORM\JoinColumn(name="soggetto_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $soggetto;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $bando;

    public function getProgramma(): ?TC4Programma {
        return $this->programma;
    }

    public function getCausaleTrasferimento(): ?TC49CausaleTrasferimento {
        return $this->causale_trasferimento;
    }

    public function getCodTrasferimento(): ?string {
        return $this->cod_trasferimento;
    }

    public function getDataTrasferimento(): ?\DateTime {
        return $this->data_trasferimento;
    }

    public function getImportoTrasferimento(): ?string {
        return $this->importo_trasferimento;
    }

    public function getSoggetto(): ?Soggetto {
        return $this->soggetto;
    }

    public function setProgramma(TC4Programma $programma): self {
        $this->programma = $programma;

        return $this;
    }

    public function setCausaleTrasferimento(TC49CausaleTrasferimento $causale_trasferimento): self {
        $this->causale_trasferimento = $causale_trasferimento;

        return $this;
    }

    public function setCodTrasferimento(string $cod_trasferimento): self {
        $this->cod_trasferimento = $cod_trasferimento;

        return $this;
    }

    public function setDataTrasferimento(\DateTime $data_trasferimento): self {
        $this->data_trasferimento = $data_trasferimento;

        return $this;
    }

    public function setImportoTrasferimento($importo_trasferimento): self {
        $this->importo_trasferimento = $importo_trasferimento;

        return $this;
    }

    public function setSoggetto(Soggetto $soggetto): self {
        $this->soggetto = $soggetto;

        return $this;
    }

    public function getBando(): ?Procedura {
        return $this->bando;
    }

    public function setBando(Procedura $bando) {
        $this->bando = $bando;
    }

    public function __toString(): ?string {
        return $this->cod_trasferimento;
    }
}
