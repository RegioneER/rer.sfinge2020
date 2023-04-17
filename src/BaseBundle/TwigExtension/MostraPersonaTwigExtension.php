<?php

namespace BaseBundle\TwigExtension;

use BaseBundle\Controller\BaseController;
use SoggettoBundle\Entity\Azienda;
use SoggettoBundle\Entity\Soggetto;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MostraPersonaTwigExtension extends \Twig_Extension {

    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function getName()
    {
        return 'base_mostra_dati_persona';
    }


    public function mostraPersona($persona){
        if(is_null($persona)){
            throw new \Exception("Occorre indicare un soggetto o per id o per oggetto");
        }

        if(!is_object($persona)){
            //faccio la ricrca per id
            $persona = $this->container->get("doctrine")->getRepository("AnagraficheBundle:Persona")->find($persona);
        }
        return $this->container->get("templating")->render("BaseBundle:Base:mostraPersona.html.twig", array("_persona" => $persona));

    }

    public function getFunctions()
    {
        return array(
			new \Twig_SimpleFunction('mostra_persona', array($this, 'mostraPersona'), array('is_safe' => array('html'))),

        );
    }
}