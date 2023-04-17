<?php

namespace RichiesteBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Entity\Proponente;


interface IGestoreStatoRichiesta
{
	public function visualizzaPianoCosti(Proponente $proponente): Response;
	
	public function getVociMenu(): array;
}