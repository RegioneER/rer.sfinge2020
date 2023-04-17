<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_ap01")
 */
class VistaAP01 {
    use StrutturaRichiestaTrait;
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="TC1ProceduraAttivazione")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc1_procedura_attivazione;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    public function getTc1ProceduraAttivazione(): ?TC1ProceduraAttivazione {
        return $this->tc1_procedura_attivazione;
    }

    public function setTc1ProceduraAttivazione(TC1ProceduraAttivazione $tc1_procedura_attivazione): self {
        $this->tc1_procedura_attivazione = $tc1_procedura_attivazione;
        return $this;
    }

    public function getCodLocaleProgetto(): ?string {
        return $this->cod_locale_progetto;
    }

    public function setCodLocaleProgetto(string $cod_locale_progetto): self {
        $this->cod_locale_progetto = $cod_locale_progetto;
        return $this;
    }
}
