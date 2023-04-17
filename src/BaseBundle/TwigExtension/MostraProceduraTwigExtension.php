<?php

namespace BaseBundle\TwigExtension;

use BaseBundle\Controller\BaseController;
use SfingeBundle\Entity\Procedura;
use SoggettoBundle\Entity\Azienda;
use SoggettoBundle\Entity\Soggetto;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MostraProceduraTwigExtension extends \Twig_Extension {

    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'base_mostra_dati_procedura';
    }

    public function mostraProcedura($procedura, $tipo){
        if(is_null($procedura)){
            throw new \Exception("Occorre indicare una procedura o per id o per oggetto");
        }
        if(is_null($tipo)){
            throw new \Exception("Occorre indicare un tipo di procedura");
        }

        if(!is_object($procedura)){
            //faccio la ricerca per id
            $procedura = $this->container->get("doctrine")->getRepository("SfingeBundle:Procedura")->find($procedura);
        }

        if($procedura instanceof Procedura){
            return $this->container->get("templating")->render("BaseBundle:Base:mostraProcedura.html.twig", array("procedura" => $procedura, "tipo" => $tipo));
        }else{
            throw new \Exception("Nessun template trovato");
        }
    }

    public function getFunctions()
    {
        return array(
			new \Twig_SimpleFunction('mostra_procedura', array($this, 'mostraProcedura'), array('is_safe' => array('html'))),
        );
    }
}