<?php

namespace SfingeBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Routing\Router as Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage as TokenStorage;
use Symfony\Component\HttpFoundation\Session\Session as Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent as GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoginListener {

    private $authorization_checker;
    private $token_storage;
    private $router;
    private $session;
    private $container;

    public function __construct(Router $router, AuthorizationChecker $authorization_checker, TokenStorage $token_storage, Session $session, ContainerInterface $container) {
        $this->authorization_checker = $authorization_checker;
        $this->token_storage = $token_storage;
        $this->router = $router;
        $this->session = $session;
        $this->container = $container;
    }

    public function controlliUtenza(GetResponseEvent $event) {

        if (($this->token_storage->getToken() ) && ( $this->authorization_checker->isGranted('IS_AUTHENTICATED_FULLY') )) {
            //i route da escludere e da non gestire vanno quì
            $route_skip = array("_wdt", "_profiler", "comuni_provincia_options", "comuni_provincia_options_persona");
            $route_skip_manutenzione = array("manutenzione", "logout", "fos_user_security_logout");

            $route_name = $event->getRequest()->get('_route');
            $utente = $this->token_storage->getToken()->getUser();
            $manutenzione = $this->container->get("doctrine")->getRepository("SfingeBundle:ParametroSistema")->findOneByCodice('MANUTENZIONE');
            if ($manutenzione->getValore() == 'true' && !in_array($route_name, $route_skip_manutenzione)) {
                if (!$this->authorization_checker->isGranted('ROLE_SUPER_ADMIN')) {
                    $response = new RedirectResponse($this->router->generate('manutenzione'));
                    $event->setResponse($response);
                    return;
                }
            }

            if (in_array($route_name, $route_skip)) {
                return;
            }

            // $this->logRequest($utente, $route_name);
            if (!$utente->getDatiPersonaInseriti()) {
                if (!is_null($route_name) && !($route_name == 'registra_persona')) {
                    $response = new RedirectResponse($this->router->generate('registra_persona', array("id_utente" => $utente->getId())));
                    $this->session->getFlashBag()->add('warning', "Si prega di inserire i dati personali");
                    $event->setResponse($response);
                }
            }
            
            if ($utente->haDoppioRuoloInvFesr() ) {
                if (!$utente->haDoppioRuoloInvFesrImpostato()) {
                    if (!is_null($route_name) && !($route_name == 'selezione_ruoli') && !($route_name == 'seleziona_ruolo')) {
                        $response = new RedirectResponse($this->router->generate('selezione_ruoli'));
                        $this->session->getFlashBag()->add('warning', "Si prega di scegliere il ruolo");
                        $event->setResponse($response);
                    }
                }
            }
        }
    }
    

    private function logRequest($utente, $route_name) {
        if (!$this->container->hasParameter('gl')) {
            return;
        }

        $params = $this->container->getParameter('gl');

        if (!array_key_exists("stream", $params) || !array_key_exists("auth", $params)) {
            return;
        }

        $streamName = $params["stream"];
        $authentication = $params["auth"];

        $username_utente = $utente->getUsername();
        $message = "Accesso alla route " . $route_name;
        $GELFLog = new \BaseBundle\Service\GELFLog($streamName, $authentication);
        $GELFLog->message->setFacility("authentication");
        $GELFLog->message->setShortMessage($message);
        $GELFLog->message->setLevel(\BaseBundle\Service\GelfLogger::INFO);

        $additionalData = array();
        $additionalData['username'] = $username_utente;
        $now = new \DateTime();
        $additionalData['time'] = $now->format('Y-m-d H:i:s');

        foreach ($additionalData as $key => $value) {
            $GELFLog->message->setAdditional($key, $value);
        }

        $GELFLog->message->setFullMessage($message);

        $GELFLog->publish();
    }

}
