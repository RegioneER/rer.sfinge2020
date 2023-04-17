<?php
/**
 * Created by PhpStorm.
 * User: giuseppe.dibona
 * Date: 2019-02-14
 * Time: 10:16
 */

namespace FunzioniServizioBundle\EventListener;

use SfingeBundle\Entity\Utente;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FunzioniServizioListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * PrivacyListener constructor.
     *
     * @param TokenStorageInterface $t
     * @param RouterInterface       $r
     */
    public function __construct(TokenStorageInterface $t, RouterInterface $r)
    {
        $this->tokenStorage = $t;
        $this->router = $r;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if($this->isUserLogged() && $event->isMasterRequest()) {
            /** @var Utente $user */
            $user = $this->tokenStorage->getToken()->getUser();
            $route = $event->getRequest()->attributes->get('_route');

            if(in_array("ROLE_VERIFICHE_ESTERNE", $user->getRoles()) && $route === 'home') {
                $event->setResponse(new RedirectResponse($this->router->generate('funzioni_servizio_index')));
            }
        }
    }

    /**
     * @return bool
     */
    private function isUserLogged()
    {
        $user = null;

        if($this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();
        }

        return $user instanceof Utente;
    }
}