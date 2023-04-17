<?php


namespace SoggettoBundle\Form\Entity;

use AnagraficheBundle\Entity\Persona;
use DateTime;
use SoggettoBundle\Entity\ComuneUnione;

/**
 * Class NuovoComune
 */
class NuovoComune
{
    /** @var ComuneUnione */
    public $comune;

    /** @var Persona */
    public $legaleRappresentante;

    /**
     * NuovaAzienda constructor.
     *
     * @throws \Exception
     */
    public function __construct() {
        $this->comune = new ComuneUnione();
        $this->comune->setDataRegistrazione(new DateTime());
        $this->comune->setSitoWeb("http://");

        $this->legaleRappresentante = new Persona();
    }
}