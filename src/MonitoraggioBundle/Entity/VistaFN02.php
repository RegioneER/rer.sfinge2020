<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_fn02")
 */
class VistaFN02 {
    use StrutturaRichiestaTrait;
    use HasCodLocaleProgetto;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="TC37VoceSpesa")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc37_voce_spesa;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=false)
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $importo;

    public function setImporto($importo): self {
        $importo_pulito = str_replace(',', '.', $importo);
        $this->importo = (float) $importo_pulito;

        return $this;
    }

    public function getImporto() {
        return $this->importo;
    }

    public function setTc37VoceSpesa(TC37VoceSpesa $tc37VoceSpesa): self {
        $this->tc37_voce_spesa = $tc37VoceSpesa;

        return $this;
    }

    public function getTc37VoceSpesa(): ?TC37VoceSpesa {
        return $this->tc37_voce_spesa;
    }
}
