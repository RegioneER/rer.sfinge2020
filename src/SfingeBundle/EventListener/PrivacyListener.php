<?php

namespace SfingeBundle\EventListener;

use SfingeBundle\Entity\Utente;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session as Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage as TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as AuthorizationChecker;

class PrivacyListener
{
    private $authorization_checker;
    private $token_storage;
    private $router;

    public function __construct(Router $router, AuthorizationChecker $authorization_checker, TokenStorage $token_storage)
    {
        $this->authorization_checker = $authorization_checker;
        $this->token_storage = $token_storage;
        $this->router = $router;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $route_name = $event->getRequest()->get('_route');

        if ($route_name === 'accettazionePrivacy') {
            return;
        }

        if ($route_name === 'fos_user_security_logout') {
            return;
        }

        if ($route_name === 'federa_user_registration_register') {
            return;
        }

        if ($route_name === 'registra_persona') {
            return;
        }

        if ($route_name === 'comuni_provincia_options') {
            return;
        }

        if ($route_name === 'comuni_provincia_options_persona') {
            return;
        }

        if(($this->token_storage->getToken()) && ($this->authorization_checker->isGranted('IS_AUTHENTICATED_FULLY'))) {

            /** @var Utente $utente */
            $utente = $this->token_storage->getToken()->getUser();

            if($utente->getPrivacyAccettata()) {
                return;
            }

            $response = new RedirectResponse($this->router->generate('accettazionePrivacy'));
            $event->setResponse($response);
        }
    }
}