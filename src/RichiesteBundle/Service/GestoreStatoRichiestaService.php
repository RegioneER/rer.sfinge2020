<?php

namespace RichiesteBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;


class GestoreStatoRichiestaService
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function getGestore(Richiesta $richiesta): IGestoreStatoRichiesta
	{
		return new GestoreStatoRichiestaBase($this->container, $richiesta);
	}
}