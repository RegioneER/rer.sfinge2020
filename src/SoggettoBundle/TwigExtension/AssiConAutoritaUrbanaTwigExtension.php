<?php

namespace SoggettoBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AssiConAutoritaUrbanaTwigExtension extends \Twig_Extension {

    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function getName()
    {
        return 'assi_con_au';
    }

    public function assiConAutoritaUrbane()
	{        
        return $this->container->get("doctrine")->getRepository("SfingeBundle\Entity\Asse")->getAssiConAutoritaUrbane();
    }


    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('assi_con_au', array($this, 'assiConAutoritaUrbane'), array('is_safe' => array('html'))),

        );
    }
}