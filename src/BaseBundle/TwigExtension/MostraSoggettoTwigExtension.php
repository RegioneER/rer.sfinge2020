<?php

namespace BaseBundle\TwigExtension;

use BaseBundle\Controller\BaseController;
use SoggettoBundle\Entity\Azienda;
use SoggettoBundle\Entity\Soggetto;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MostraSoggettoTwigExtension extends \Twig_Extension {

    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function getName()
    {
        return 'base_mostra_dati_soggetto';
    }

    public function mostraSoggettoRappresentato(){
        $soggetto = $this->container->get("session")->get(BaseController::SESSIONE_SOGGETTO);
        if(is_null($soggetto)){
            return;
        }
        return $this->container->get("templating")->render("BaseBundle:Base:mostraSoggettoRappresentato.html.twig", array("soggetto" => $soggetto));
    }

    public function mostraSoggettoIstruendo(){
        $soggetto = $this->container->get("session")->get(BaseController::SESSIONE_SOGGETTO_ISTRUENDO);
        if(is_null($soggetto)){
            return;
        }
        return $this->container->get("templating")->render("BaseBundle:Base:mostraSoggettoIstruendo.html.twig", array("soggetto" => $soggetto));
    }
	
    public function mostraSoggetto($soggetto){
        if(is_null($soggetto)){
            throw new \Exception("Occorre indicare un soggetto o per id o per oggetto");
        }

        if(!is_object($soggetto)){
            //faccio la ricrca per id
            $soggetto = $this->container->get("doctrine")->getRepository("SoggettoBundle:Soggetto")->find($soggetto);
        }

        if($soggetto instanceof Azienda){
            return $this->container->get("templating")->render("BaseBundle:Base:mostraAzienda.html.twig", array("soggetto" => $soggetto));
        }else if($soggetto instanceof Soggetto){
            return $this->container->get("templating")->render("BaseBundle:Base:mostraSoggetto.html.twig", array("soggetto" => $soggetto));
        }else{
            throw new \Exception("Nessun template trovato");
        }
    }

    public function getFunctions()
    {
        return array(
			new \Twig_SimpleFunction('mostra_soggetto_rappresentato', array($this, 'mostraSoggettoRappresentato'), array('is_safe' => array('html'))),
			new \Twig_SimpleFunction('mostra_soggetto', array($this, 'mostraSoggetto'), array('is_safe' => array('html'))),
			new \Twig_SimpleFunction('mostra_soggetto_istruendo', array($this, 'mostraSoggettoIstruendo'), array('is_safe' => array('html'))),
        );
    }
}