<?php

namespace FascicoloBundle\TwigExtension;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\TwigFunction;

class SerializeTwigExtension extends \Twig_Extension {

    /**
     * @var EntityManagerInterface
     */
    protected $em;
    protected $container;

    public function __construct(EntityManagerInterface $em = null, ContainerInterface $container = null) {
        $this->em = $em;
        $this->container = $container;
    }

    public function getName() {
        return 'serialize_extension';
    }

    public function serialize($object): string {
        return serialize($object);
    }

    public function quote($object): string {
        $connection = $this->em->getConnection();
        return $connection->quote($object);
    }

    public function getFilters() {
        return [
            new \Twig_SimpleFilter('serialize', [$this, 'serialize']),
            new \Twig_SimpleFilter('quote', [$this, 'quote']),
        ];
    }

    public function isVisible($frammento, $aliasFascicolo, $path_pagina, $istanzaFascicolo) {
        $callbackPresenzaFrammento = $frammento->getCallbackPresenza();
        $servizioIstanzaFascicolo = $this->container->get("fascicolo.istanza." . $aliasFascicolo);
        if (method_exists($servizioIstanzaFascicolo, $callbackPresenzaFrammento)) {
            $presente = $servizioIstanzaFascicolo->$callbackPresenzaFrammento($istanzaFascicolo, $path_pagina . "." . $frammento->getAlias());
            return $presente;
        }
        return true;
    }

    public function getFunctions() {
        return [
            new TwigFunction('is_visible', [$this, 'isVisible'])
        ];
    }

}
