<?php

namespace BaseBundle\Log;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Session\Session;

class ClientIpProcessor {

	private $requestStack;
	private $container;
	private $session;
	private $cachedClientIp;
	private $user = null;
	private $lastToken = '';

	public function __construct(ContainerInterface $container, RequestStack $requestStack, Session $session) {
		$this->requestStack = $requestStack;
		$this->container = $container;
		$this->session = $session;
	}

	public function __invoke(array $record) {

		$record['extra']['client_ip'] = $this->cachedClientIp ? $this->cachedClientIp : 'unavailable';

		$token = $this->container->get('security.token_storage')->getToken();

		if ($token == null) {
			try {
				$this->session->start();
				$token = $this->session->getId();
			} catch (\RuntimeException $e) {
				$token = 'errore nel log dei messaggi';
			}
		}

		if ($token instanceof UsernamePasswordToken) {
			$record['extra']['user'] = $token->getUsername();
			$record['extra']['token'] = $this->lastToken;
			$this->user = $token->getUsername();
			$this->session->set("username", $this->user);
		}

		if (!isset($this->user) && $this->session->has("username")) {
			$this->user = $this->session->get("username");
		}

		if (is_null($this->user)) {
			$this->user = "anon.";
		}

		$this->lastToken = $this->session->getId();

		$record['extra']['user'] = $this->user;
		$record['extra']['token'] = $this->lastToken;

		if ($record['extra']['client_ip'] !== 'unavailable') {
			return $record;
		}

		if (!$request = $this->requestStack->getCurrentRequest()) {
			return $record;
		}


		if ($request->headers->has("RERFwFor")) {
			$this->cachedClientIp = $request->headers->get("RERFwFor");
		} else {
			$this->cachedClientIp = $request->getClientIp();
		}
		
		$record['extra']['client_ip'] = $this->cachedClientIp;

		if ($this->session->getId() != "") {
			$record["level"] = 0;
		}

		return $record;
	}

}
