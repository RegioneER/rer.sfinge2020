<?php

namespace BaseBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ModaleRicercaTwigExtension extends \Twig_Extension {

    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function getName()
    {
        return 'base_mostra_modale_ricerca';
    }

    public function mostraModaleRicerca($titolo, $form, $routeAnnulla, $id_modale, $parametriRouteAnnulla = array()){

        return $this->container->get("templating")->render("BaseBundle:Base:modaleRicerca.html.twig",
            array("titolo" => $titolo,"form"=>$form,"route_annulla"=>$routeAnnulla, "id_modale"=>$id_modale, 'parametriRouteAnnulla' => $parametriRouteAnnulla));
    }

    public function getFunctions()
    {
        return array(
			new \Twig_SimpleFunction('modale_ricerca', array($this, 'mostraModaleRicerca'), array('is_safe' => array('html'))),

        );
    }
}