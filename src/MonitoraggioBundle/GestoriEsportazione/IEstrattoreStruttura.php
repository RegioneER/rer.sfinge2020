<?php

namespace MonitoraggioBundle\GestoriEsportazione;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;


interface IEstrattoreStruttura
{
	public function __construct(ContainerInterface $container);
	
	public function generateResult(): Response;
}