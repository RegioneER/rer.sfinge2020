<?php


namespace SoggettoBundle\Form\Entity;

use AnagraficheBundle\Entity\Persona;
use DateTime;
use SoggettoBundle\Entity\Soggetto;

/**
 * Class NuovoSoggetto
 */
class NuovoSoggetto
{
    /** @var Soggetto */
    public $soggetto;

    /** @var Persona */
    public $legaleRappresentante;

    /**
     * NuovaAzienda constructor.
     *
     * @throws \Exception
     */
    public function __construct() {
        $this->soggetto = new Soggetto();
        $this->soggetto->setDataRegistrazione(new DateTime());
        $this->soggetto->setSitoWeb("http://");

        $this->legaleRappresentante = new Persona();
    }
}