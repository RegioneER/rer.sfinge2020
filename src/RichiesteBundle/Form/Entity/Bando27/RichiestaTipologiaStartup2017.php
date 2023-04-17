<?php


namespace RichiesteBundle\Form\Entity\Bando27;

use RichiesteBundle\Entity\Richiesta;

class RichiestaTipologiaStartup2017 extends Richiesta
{
	protected $multi_proponente;
	
	protected $tipologia;

	public function getMultiProponente()
	{
		return $this->multi_proponente;
	}

	public function setMultiProponente($multi_proponente)
	{
		$this->multi_proponente = $multi_proponente;
	}

	public function getTipologia() {
		return $this->tipologia;
	}

	public function setTipologia($tipologia) {
		$this->tipologia = $tipologia;
	}



}