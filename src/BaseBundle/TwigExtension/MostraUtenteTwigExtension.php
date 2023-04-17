<?php

namespace BaseBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class MostraUtenteTwigExtension extends \Twig_Extension {

	private $container;

	function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	public function getName() {
		return 'base_mostra_dati_utente';
	}

	public function mostraUtente($utente) {
		if (is_null($utente)) {
			throw new \Exception("Occorre indicare un utente o per id o per oggetto");
		}

		if (!is_object($utente)) {
			$utente = $this->container->get("doctrine")->getRepository("SfingeBundle:Utente")->find($utente);
		}
		return $this->container->get("templating")->render("BaseBundle:Base:mostraUtente.html.twig", array("utente" => $utente));
	}

	public function getFunctions() {
		return array(
			new \Twig_SimpleFunction('mostra_utente', array($this, 'mostraUtente'), array('is_safe' => array('html'))),
		);
	}

}
