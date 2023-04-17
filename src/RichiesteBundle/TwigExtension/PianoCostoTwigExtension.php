<?php

namespace RichiesteBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class PianoCostoTwigExtension extends \Twig_Extension {

    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function getName()
    {
        return 'piano_costo';
    }

    public function getAnnualitaPianoCosto($id_proponente){

        $richiesta = $this->container->get("doctrine")->getRepository("RichiesteBundle:Proponente")->find($id_proponente)->getRichiesta();

        $annualita = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualita($id_proponente);

        return $annualita;
    }


    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('annualita_piano_costo', array($this, 'getAnnualitaPianoCosto')),

        );
    }
}