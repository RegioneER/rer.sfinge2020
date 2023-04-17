<?php

namespace SoggettoBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="sedi_versions")
 */
class SedeVersion extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=1024, nullable=true)
	 */
	private $denominazione;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $numero_rea;

	/**
	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\Indirizzo", cascade={"persist"})
	 * @ORM\JoinColumn(name="indirizzo_id", nullable=false)
	 */
	private $indirizzo;

	/**
	 * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Sede")
	 * @ORM\JoinColumn(name="sede_id", nullable=false)
	 */
	private $sede;

	/**
	 * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Ateco", inversedBy="sede")
	 * @ORM\JoinColumn(name="ateco_id")
	 */
	private $ateco;
	
	/**
	 * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Ateco")
	 * @ORM\JoinColumn(name="ateco_secondario_id", referencedColumnName="id", nullable=true)) 
	 */
	private $ateco_secondario;

	function getId() {
		return $this->id;
	}

	function getDenominazione() {
		return $this->denominazione;
	}

	function getNumeroRea() {
		return $this->numero_rea;
	}

	/**
	 * @return \BaseBundle\Entity\Indirizzo
	 */
	function getIndirizzo() {
		return $this->indirizzo;
	}

	function getSede() {
		return $this->sede;
	}

	function getAteco() {
		return $this->ateco;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setDenominazione($denominazione) {
		$this->denominazione = $denominazione;
	}

	function setNumeroRea($numero_rea) {
		$this->numero_rea = $numero_rea;
	}

	function setIndirizzo($indirizzo) {
		$this->indirizzo = $indirizzo;
	}

	function setSede($sede) {
		$this->sede = $sede;
	}

	function setAteco($ateco) {
		$this->ateco = $ateco;
	}
	
	public function getAtecoSecondario() {
		return $this->ateco_secondario;
	}

	public function setAtecoSecondario($ateco_secondario) {
		$this->ateco_secondario = $ateco_secondario;
	}

}
