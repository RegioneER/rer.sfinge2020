<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_pr01")
 */
class VistaPR01 {
    use StrutturaRichiestaTrait;
    use HasCodLocaleProgetto;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="TC47StatoProgetto")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @var TC47StatoProgetto
     */
    protected $tc47_stato_progetto;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     * @var \DateTime
     */
    protected $data_riferimento;

    public function setDataRiferimento(\DateTime $dataRiferimento): self {
        $this->data_riferimento = $dataRiferimento;

        return $this;
    }

    public function getDataRiferimento(): ?\DateTime {
        return $this->data_riferimento;
    }

    public function setTc47StatoProgetto(TC47StatoProgetto $tc47StatoProgetto): self {
        $this->tc47_stato_progetto = $tc47StatoProgetto;

        return $this;
    }

    public function getTc47StatoProgetto(): ?TC47StatoProgetto {
        return $this->tc47_stato_progetto;
    }
}
