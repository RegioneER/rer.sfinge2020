<?php

namespace SfingeBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Routing\Router as Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage as TokenStorage;
use Symfony\Component\HttpFoundation\Session\Session as Session;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FascicoloListener {

    private $authorization_checker;
    private $token_storage;
    private $router;
    private $session;
	private $doctrine;
	private $pagina;

    public function __construct(Router $router, AuthorizationChecker $authorization_checker, TokenStorage $token_storage, Session $session, \Doctrine\Bundle\DoctrineBundle\Registry $doctrine, $pagina) {
        $this->authorization_checker = $authorization_checker;
        $this->token_storage = $token_storage;
        $this->router = $router;
        $this->session = $session;
		$this->doctrine = $doctrine;
		$this->pagina = $pagina;
    }

    public function accessoIstanzaFascicolo(\FascicoloBundle\Event\IstanzaFascicoloEvent $event) {
		$istanzaFascicolo = $event->getIstanzaFascicolo();
		
		// $this->pagina->resettaBreadcrumb();
		// $response = new RedirectResponse($this->router->generate('crea_persona', array("id_utente" => $utente->getId())));
		// $event->setResponse($this->)
		return;
    }

}
