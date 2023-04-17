<?php

namespace RichiesteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityTipo;

/**
 * TipologiaNaturaLaboratorio
 *
 * @ORM\Table(name="tipologie_natura_laboratorio")
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\TipologiaNaturaLaboratorioRepository")
 */
class TipologiaNaturaLaboratorio extends EntityTipo
{
    /**
	 * @var boolean $required
	 * 
	 * @ORM\Column(name="pubblico", type="boolean", nullable=false, options={"default": 0})
	 */
	protected $pubblico;
	
	/**
	 * @var boolean $required
	 * 
	 * @ORM\Column(name="attivita_economica", type="boolean", nullable=false, options={"default": 0})
	 */
	protected $attivita_economica;
	
	public function getPubblico() {
		return $this->pubblico;
	}

	public function getAttivitaEconomica() {
		return $this->attivita_economica;
	}

	public function setPubblico($pubblico) {
		$this->pubblico = $pubblico;
	}

	public function setAttivitaEconomica($attivita_economica) {
		$this->attivita_economica = $attivita_economica;
	}
    
    public function getTipologiaRichiesta8(){
        return !$this->getAttivitaEconomica() ? "A" : "B";
    }

}
