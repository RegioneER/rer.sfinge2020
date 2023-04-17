<?php

namespace BaseBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class FormPrototypeTwigExtension extends \Twig_Extension {

    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function getName()
    {
        return 'base_form_prototype';
    }

    public function renderFormPrototype($form_prototype_field)
    {
        return $this->container->get("templating")->render("BaseBundle:Base:formPrototype.html.twig",
            array("prototype_field" => $form_prototype_field));
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('form_prototype', array($this, 'renderFormPrototype'), array('is_safe' => array('html'))),

        );
    }
}
