<?php

namespace BaseBundle\TwigExtension;

use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Http\AccessMapInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * AccessibilitaUrlTwigExtension
 *
 * @author Antonio Turdo <aturdo@schema31.it>
 */
class AccessibilitaUrlTwigExtension extends \Twig_Extension
{
    private $tokenStorage;
    private $accessDecisionManager;
    private $map;
    private $authManager;
	private $router;

    public function __construct(TokenStorageInterface $tokenStorage, AccessDecisionManagerInterface $accessDecisionManager, AccessMapInterface $map, AuthenticationManagerInterface $authManager, $router)
    {
        $this->tokenStorage = $tokenStorage;
        $this->accessDecisionManager = $accessDecisionManager;
        $this->map = $map;
        $this->authManager = $authManager;
		$this->router = $router;
    }

    /**
     * Handles access authorization.
     *
     * @throws AccessDeniedException
     * @throws AuthenticationCredentialsNotFoundException
     */
    public function isAccessibile($name, $parameters = array())
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            throw new AuthenticationCredentialsNotFoundException('A Token was not found in the TokenStorage.');
        }
		
		$baseUrl = $this->router->getContext()->getBaseUrl();
		$uri = substr($this->router->generate($name, $parameters), \strlen($baseUrl));
        $request = \Symfony\Component\HttpFoundation\Request::create($uri);

        list($attributes) = $this->map->getPatterns($request);

        if (null === $attributes) {
            return;
        }

        if (!$token->isAuthenticated()) {
            $token = $this->authManager->authenticate($token);
            $this->tokenStorage->setToken($token);
        }
	
        return $this->accessDecisionManager->decide($token, $attributes, $request);
		
    }
	
    public function getName()
    {
        return 'base_accessibilita_url';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('isAccessibile', array($this, 'isAccessibile')),
        );
    }	
}
