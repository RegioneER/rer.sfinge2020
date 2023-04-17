<?php

namespace MonitoraggioBundle\Entity;

use SfingeBundle\Entity\Procedura;
use Doctrine\ORM\Mapping as ORM;

trait StrutturaProceduraTrait {
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura")
     * @ORM\JoinColumn
     * @var Procedura
     */
    protected $procedura;

    public function getProcedura(): ?Procedura {
        return $this->procedura;
    }

    public function setprocedura(Procedura $procedura): self {
        $this->procedura = $procedura;
    }
}
