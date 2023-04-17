<?php

namespace BaseBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\TwigTest;

class IndietroTwigExtension extends \Twig_Extension {

	private $container;

	function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	public function getName() {
		return 'base_pulsante_indietro';
	}

	public function pulsanteIndietro($route = null) {
		if ($route == null) {
			$request = $this->container->get("request_stack")->getCurrentRequest();
			$referer = $request->headers->get('referer');
			return $this->container->get("templating")->render("BaseBundle:Base:pulsanteIndietro.html.twig", array("url" => $referer));
		} else {
			return $this->container->get("templating")->render("BaseBundle:Base:pulsanteIndietro.html.twig", array("url" => $route));
		}
	}

	public function getFunctions() {
		return array(
			new \Twig_SimpleFunction('pulsante_indietro', array($this, 'pulsanteIndietro'), array('is_safe' => array('html'))),
		);
	}

	function getTests()
	{
		return [
			new TwigTest('instanceof',[$this, 'instanceOf'])
		];
	}

	public function instanceOf(object $oggetto, string $classe): bool {
		$refl = new \ReflectionObject($oggetto);
		
		return $refl->getName() == $classe;
	}

}
