<?php


namespace RichiesteBundle\Form\Entity\Bando4;
use AnagraficheBundle\Entity\Persona;

use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;


class RichiestaTipologia 
{
	/**
	 * @var Persona
	 * @Assert\NotNull()
	 */
	protected $firmatario;

	/**
	 * @var string
	 * @Assert\NotNull()
	 */
	protected $tipologia;

	public function getTipologia(): ?string {
		return $this->tipologia;
	}

	public function setTipologia(?string $tipologia) {
		$this->tipologia = $tipologia;
	}

	public function getFirmatario(): ?Persona {
		return $this->firmatario;
	}

	public function setFirmatario(?Persona $firmatario) {
		$this->firmatario = $firmatario;
	}
}