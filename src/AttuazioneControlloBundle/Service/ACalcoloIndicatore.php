<?php

namespace AttuazioneControlloBundle\Service;

use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ACalcoloIndicatore implements ICalcolaValoreRealizzatoIndicatoreOutput {
    /**
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container, Richiesta $richiesta) {
        $this->container = $container;
        $this->richiesta = $richiesta;
    }

    abstract public function getValore(): float;
}
