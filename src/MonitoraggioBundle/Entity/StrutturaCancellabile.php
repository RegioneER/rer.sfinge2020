<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait StrutturaCancellabile {
    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\Length(max="1", maxMessage="sfinge.monitoraggio.maxLength")
     * @Assert\Regex(pattern="(S|N)", match=true, message="Valore flag cancellazione non valido", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $flg_cancellazione;

    public function getFlgCancellazione(): ?string {
        return $this->flg_cancellazione;
    }

    public function setFlgCancellazione(?string $flg_cancellazione): self {
        $this->flg_cancellazione = '' == $flg_cancellazione ? null : $flg_cancellazione;
        return $this;
    }
}
