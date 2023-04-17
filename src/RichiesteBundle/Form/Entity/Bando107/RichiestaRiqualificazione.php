<?php


namespace RichiesteBundle\Form\Entity\Bando107;

use BaseBundle\Service\AttributiRicerca;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaRiqualificazione extends Richiesta
{
	/**
	 * @Assert\NotBlank()
	 */
	protected $tipologia;
    
    public function getTipologia() {
		return $this->tipologia;
	}

	public function setTipologia($tipologia) {
		$this->tipologia = $tipologia;
	}

}