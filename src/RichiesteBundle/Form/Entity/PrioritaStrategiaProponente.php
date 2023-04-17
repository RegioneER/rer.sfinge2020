<?php

namespace RichiesteBundle\Form\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class PrioritaStrategiaProponente 
{
	
	/**
	 * @Assert\NotNull(groups={"sistema_produttivo"})
	 */
	private $sistema_produttivo;
	
	/**
	 * @Assert\NotNull(groups={"orientamento_tematico"})
	 */	
	private $orientamento_tematico;
	
	/**
	 * @Assert\Count(min=1, groups={"priorita_tecnologiche"})
	 */
	private $priorita_tecnologiche = array();

    private $drivers;

    private $kets;
	
	private $coerenza_obiettivi;
	
	function getSistemaProduttivo() {
		return $this->sistema_produttivo;
	}

	function getOrientamentoTematico() {
		return $this->orientamento_tematico;
	}

	function getPrioritaTecnologiche() {
		return $this->priorita_tecnologiche;
	}

	function getDrivers() {
		return $this->drivers;
	}

	function getKets() {
		return $this->kets;
	}

	function setSistemaProduttivo($sistema_produttivo) {
		$this->sistema_produttivo = $sistema_produttivo;
	}

	function setOrientamentoTematico($orientamento_tematico) {
		$this->orientamento_tematico = $orientamento_tematico;
	}

	function setPrioritaTecnologiche($priorita_tecnologiche) {
		$this->priorita_tecnologiche = $priorita_tecnologiche;
	}

	function setDrivers($drivers) {
		$this->drivers = $drivers;
	}

	function setKets($kets) {
		$this->kets = $kets;
	}
	
	public function getCoerenzaObiettivi() {
		return $this->coerenza_obiettivi;
	}

	public function setCoerenzaObiettivi($coerenza_obiettivi) {
		$this->coerenza_obiettivi = $coerenza_obiettivi;
	}
			
	function addPrioritaTecnologica($prioritaTecnologica){
		$this->priorita_tecnologiche[] = $prioritaTecnologica;
	}
		
	public function getType()
	{
		return "RichiesteBundle\Form\PrioritaStrategiaProponenteType";
	}

	
}