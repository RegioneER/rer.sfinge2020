<?php

/**
 * User: rstronati
 * Date: 21/12/15
 * Time: 11:57
 */

namespace PaginaBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Symfony\Bundle\FrameworkBundle\Routing\Router as Router;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\PaginaInfo;

class PaginaListener {

	/**
	 * @var Reader
	 */
	private $reader;
	/**
	 * @var \Twig_Environment
	 */
	private $twig;
	private $router;

	public function __construct(Reader $reader, \Twig_Environment $twig, Router $router) {
		$this->reader = $reader;
		$this->twig = $twig;
		$this->router = $router;
	}

	/**
	 * This event will fire during any controller call
	 */
	public function onKernelController(FilterControllerEvent $event) {
		if (!is_array($controller = $event->getController())) {
			return;
		}

		$request = $event->getRequest();

		$object = new \ReflectionObject($controller[0]);
		$method = $object->getMethod($controller[1]);

		$annotazioni = array();
		$annotazione_classe = $this->reader->getClassAnnotation($object, "PaginaBundle\Annotations\Breadcrumb");
		$annotazione_metodo = $this->reader->getMethodAnnotation($method, "PaginaBundle\Annotations\Breadcrumb");

		if ($annotazione_classe) {
			$annotazioni[] = $annotazione_classe;
		}

		if ($annotazione_metodo) {
			$annotazioni[] = $annotazione_metodo;
		}

		$elementiBreadcrumb = array();
		foreach ($annotazioni as $annotazione) {
			$annotazioneReflection = new \ReflectionObject($annotazione);
			$proprieta = $annotazioneReflection->getProperty("elementi");
			$valori = $proprieta->getValue($annotazione);
			if (!is_null($valori)) {
				foreach ($valori as $valore) {
					if (is_null($valore->route)) {
						$url = null;
					} else {
						$parametri = array();
						if (!is_null($valore->parametri)) {
							foreach ($valore->parametri as $parametro) {
								if ($request->get($parametro)) {
									//ricavo il valore dal parametro dalla request
									$parametri[$parametro] = $event->getRequest()->get($parametro);
								}
							}
						}

						if (\count($parametri)) {
							$url = $this->router->generate($valore->route, $parametri);
						} else {
							$url = $this->router->generate($valore->route);
						}
					}
					$valore->setUrl($url);
					$elementiBreadcrumb[] = $valore;
				}
			}
		}

		if (\count($elementiBreadcrumb)) {
                $this->tryToAddGlobal("elementiBreadcrumb", $elementiBreadcrumb);
		}

		$paginainfo = $this->reader->getMethodAnnotation($method, "PaginaBundle\Annotations\PaginaInfo");
		if (!is_null($paginainfo)) {
			$annotazioneReflection = new \ReflectionObject($paginainfo);
			foreach ($annotazioneReflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $proprieta) {
				$valore = $proprieta->getValue($paginainfo);
				$nome = $proprieta->getName();
				if (!\is_null($valore)) {
					$this->tryToAddGlobal($nome, $valore);
				}
			}
		}

		$menuItem = $this->reader->getMethodAnnotation($method, "PaginaBundle\Annotations\Menuitem");
		$session = $request->getSession();
		if (!is_null($menuItem)) {
			$annotazioneReflection = new \ReflectionObject($menuItem);
			$proprieta = $annotazioneReflection->getProperty("menuAttivo");
			$valore = $proprieta->getValue($menuItem);

			if (!is_null($valore)) {
				$session->set("current_link", $valore);
			} else {
				$session->remove("current_link");
			}
		} else {
			$session->remove("current_link");
		}
	}

	protected function tryToAddGlobal($name, $value): void {
		try{
			$this->twig->addGlobal($name, $value);
		}
		catch(\LogicException $e){

		}
	}

}
