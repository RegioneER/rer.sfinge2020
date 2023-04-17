<?php

namespace MonitoraggioBundle\Entity;

use RichiesteBundle\Entity\Richiesta;
use Doctrine\ORM\Mapping as ORM;

trait StrutturaRichiestaTrait {
    /**
     * @var Richiesta|null
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta")
     */
    protected $richiesta;

    public function getRichiesta(): Richiesta {
        return $this->richiesta;
    }
}
