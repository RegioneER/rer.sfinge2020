<?php
namespace SoggettoBundle\Form\Entity;

use AnagraficheBundle\Entity\Persona;

/**
 * Class NuovaPersonaFisica
 * @package SoggettoBundle\Form\Entity
 */
class NuovaPersonaFisica
{
    /** @var Persona */
    public $legaleRappresentante;

    /**
     * NuovaPersonaFisica constructor.
     */
    public function __construct()
    {
        $this->legaleRappresentante = new Persona();
    }
}
