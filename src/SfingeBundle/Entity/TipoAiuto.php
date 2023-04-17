<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;
use MonitoraggioBundle\Entity\TC6TipoAiuto;

/**
 * @ORM\Entity
 * @ORM\Table(name="tipi_aiuti")
 */
class TipoAiuto extends EntityTipo {
    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC6TipoAiuto")
     * @ORM\JoinColumn(nullable=true)
     * @var TC6TipoAiuto
     */
    protected $tc6_tipo_aiuto;

    public function setTc6TipoAiuto(TC6TipoAiuto $tc6TipoAiuto): self {
        $this->tc6_tipo_aiuto = $tc6TipoAiuto;

        return $this;
    }

    public function getTc6TipoAiuto(): ?TC6TipoAiuto {
        return $this->tc6_tipo_aiuto;
    }
}
