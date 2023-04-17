<?php

namespace AttuazioneControlloBundle\Fascicoli;

use FascicoloBundle\Entity\IstanzaFascicolo;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PrincipiRsi20191204 {

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }
    
}
