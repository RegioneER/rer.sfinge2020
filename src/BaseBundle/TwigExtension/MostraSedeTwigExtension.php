<?php

namespace BaseBundle\TwigExtension;

use SoggettoBundle\Entity\Sede;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MostraSedeTwigExtension extends \Twig_Extension {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getName() {
        return 'base_mostra_dati_sede';
    }

    public function mostraSede($sede) {
        if (is_null($sede)) {
            throw new \Exception("Occorre indicare una sede o per id o per oggetto");
        }

        if (!is_object($sede)) {
            //faccio la ricerca per id
            $sede = $this->container->get("doctrine")->getRepository("SoggettoBundle:Sede")->find($sede);
        }

        if ($sede instanceof Sede) {
            return $this->container->get("templating")->render("BaseBundle:Base:mostraSede.html.twig", array("sede" => $sede));
        } else {
            throw new \Exception("Nessun template trovato");
        }
    }

    public function mostraSedeIntervento($richiesta) {
        if (is_null($richiesta)) {
            throw new \Exception("Occorre indicare una richiesta o per id o per oggetto");
        }

        if (!is_object($richiesta)) {
            //faccio la ricerca per id
            $richiesta = $this->container->get("doctrine")->getRepository("RichiestaBundle:Richiesta")->find($richiesta);
        }

        $sedi = $richiesta->getMandatario()->getSedi();
        return $this->container->get("templating")->render("BaseBundle:Base:mostraSediIntervento.html.twig", array("sedi" => $sedi));
    }

    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('mostra_sede', array($this, 'mostraSede'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_sede_intervento', array($this, 'mostraSedeIntervento'), array('is_safe' => array('html'))),
        );
    }

}
