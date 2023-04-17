<?php

namespace BaseBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Routing\Router as Router;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LogListener {

	private $container;

	public function __construct($container) {
		$this->container = $container;
	}

	public function onKernelRequest(GetResponseEvent $event) {
		$request = $event->getRequest();
		$logger = $this->container->get("logger");
		
		$context = array();
		$context["route"] = $request->attributes->get('_route');
		$context["params"] = $request->attributes->get('_route_params');
		$context["uri"] = $request->getRequestUri();
		// $context["full"] = $request->request->all();
		$context["user-agent"] = $request->headers->get('user-agent');
		
		$logger->debug("Request", $context);
	}

}
