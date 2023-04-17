<?php


namespace RichiesteBundle\Form\Entity\Bando6;

use RichiesteBundle\Entity\Richiesta;

class RichiestaInnovazione extends Richiesta
{
	protected $multi_proponente;
	
	public function getMultiProponente()
	{
		return $this->multi_proponente;
	}

	public function setMultiProponente($multi_proponente)
	{
		$this->multi_proponente = $multi_proponente;
	}

}