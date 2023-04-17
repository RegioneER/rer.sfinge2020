<?php

namespace SoggettoBundle\Form\Entity;

use AnagraficheBundle\Entity\Persona;
use DateTime;
use SoggettoBundle\Entity\Azienda;

/**
 * Class NuovaAzienda
 */
class NuovaAzienda {
    /** @var Azienda */
    public $azienda;

    /** @var Persona */
    public $legaleRappresentante;

    /**
     * NuovaAzienda constructor.
     *
     * @throws \Exception
     */
    public function __construct() {
        $this->azienda = new Azienda();
        $this->azienda->setDataRegistrazione(new DateTime());
        $this->azienda->setSitoWeb("http://");

        $this->legaleRappresentante = new Persona();
    }
}
