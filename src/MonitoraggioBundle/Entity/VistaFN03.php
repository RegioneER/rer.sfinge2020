<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_fn03")
 */
class VistaFN03 {
    use StrutturaRichiestaTrait;
    use HasCodLocaleProgetto;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="/^\d{4}$/", groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.invalidYear")
     * @Assert\LessThan(value=10000, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.lessthan")
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $anno_piano;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\GreaterThanOrEqual(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThanOrEqual")
     */
    protected $imp_realizzato;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\GreaterThanOrEqual(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $imp_da_realizzare;

    public function setAnnoPiano($annoPiano): self {
        $this->anno_piano = $annoPiano;

        return $this;
    }

    public function getAnnoPiano() {
        return $this->anno_piano;
    }

    public function setImpRealizzato($impRealizzato): self {
        $importo_pulito = str_replace(',', '.', $impRealizzato);
        $this->imp_realizzato = (float) $importo_pulito;

        return $this;
    }

    public function getImpRealizzato() {
        return $this->imp_realizzato;
    }

    public function setImpDaRealizzare($impDaRealizzare): self {
        $importo_pulito = str_replace(',', '.', $impDaRealizzare);
        $this->imp_da_realizzare = (float) $importo_pulito;

        return $this;
    }

    public function getImpDaRealizzare() {
        return $this->imp_da_realizzare;
    }
}
