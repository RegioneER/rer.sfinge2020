<?php

namespace RichiesteBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Routing\Router as Router;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RichiesteListener {

	const SESSIONE_SOGGETTO = "_soggetto";

	private $router;
	private $authorization_checker;
	private $token_storage;
	private $doctrine;

	public function __construct(Router $router, $authorization_checker, $token_storage, $doctrine) {
		$this->router = $router;
		$this->authorization_checker = $authorization_checker;
		$this->token_storage = $token_storage;
		$this->doctrine = $doctrine;
	}

	public function onKernelRequest(GetResponseEvent $event) {
		$request = $event->getRequest();
		$sessione = $request->getSession();
		if ($this->token_storage->getToken() &&
				$this->authorization_checker->isGranted('ROLE_UTENTE') &&
				$this->startsWith($request->attributes->get("_controller"), "RichiesteBundle\Controller\Presentazione")) {
			$em = $this->doctrine->getManager();
			$soggetto = $sessione->get(self::SESSIONE_SOGGETTO);

			if (is_null($soggetto)) {
				$sessione->getFlashBag()->add('error', "Soggetto non valido");
				$event->setResponse(new \Symfony\Component\HttpFoundation\RedirectResponse($this->router->generate("home")));
				return;
			}

			$legaleRappr = $em->getRepository("SoggettoBundle\Entity\Soggetto")->getLegaleRappresentante($soggetto);
			if (count($legaleRappr) == 0) {
				$legaleRapprDaConfermare = $em->getRepository("SoggettoBundle\Entity\Soggetto")->getLegaleRappresentanteDaConfermare($soggetto);
				if (count($legaleRapprDaConfermare) != 0) {
					$sessione->getFlashBag()->add('error', "Non risulta un legale rappresentante attivo");
				} else {
					$sessione->getFlashBag()->add('error', "Non è stato inserito alcun legale rappresentate, oppure l'incarico risulta revocato");
				}
				$event->setResponse(new \Symfony\Component\HttpFoundation\RedirectResponse($this->router->generate("elenco_incarichi")));
				return;
			}


		}
	}

	private function startsWith($haystack, $needle) {
		// search backwards starting from haystack length characters from the end
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
	}

}
