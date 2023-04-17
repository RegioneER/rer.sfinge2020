<?php

namespace RichiesteBundle\Form\Entity\Bando126;
use AnagraficheBundle\Entity\Persona;
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
	protected $categoria;
    
	protected $procedura;

	public function getCategoria(): ?string {
		return $this->categoria;
	}

	public function setCategoria(?string $categoria) {
		$this->categoria = $categoria;
	}

	public function getFirmatario(): ?Persona {
		return $this->firmatario;
	}

	public function setFirmatario(?Persona $firmatario) {
		$this->firmatario = $firmatario;
	}
    
    public function getProcedura() {
        return $this->procedura;
    }

    public function setProcedura($procedura) {
        $this->procedura = $procedura;
    }

}