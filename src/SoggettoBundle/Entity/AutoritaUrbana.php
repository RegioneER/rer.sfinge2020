<?php

namespace SoggettoBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="SoggettoBundle\Entity\AutoritaUrbanaRepository")
 * @ORM\Table(name="autorita_urbane")
 */
class AutoritaUrbana extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="string", length=1000,  name="titolo", nullable=false)
	 */
	protected $titolo;

	/**
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $ordinamento;	
	
	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Asse", inversedBy="autorita_urbana")
	 * @ORM\JoinColumn(name="asse_id", referencedColumnName="id", nullable=false)
	 */
	protected $asse;

    /**
     *  @ORM\OneToMany(targetEntity="SoggettoBundle\Entity\AzioneAutoritaUrbana", mappedBy="autorita_urbana")
     */
    protected $azioni_autorita_urbana;

	
    public function __construct() {
		$this->azioni_autorita_urbana = new \Doctrine\Common\Collections\ArrayCollection();
    }
	
	function getId() {
		return $this->id;
	}

	function getTitolo() {
		return $this->titolo;
	}

	function getOrdinamento() {
		return $this->ordinamento;
	}

	function getAsse() {
		return $this->asse;
	}

	function getAzioniAutoritaUrbana() {
		$iterator = $this->azioni_autorita_urbana->getIterator();
		$iterator->uasort(function ($a, $b) {
			return ($a->getOrdinamento() < $b->getOrdinamento()) ? -1 : 1;
		});
		return new \Doctrine\Common\Collections\ArrayCollection(iterator_to_array($iterator));				
	}

	/*  --- */
	
	function setId($id) {
		$this->id = $id;
	}

	function setTitolo($titolo) {
		$this->titolo = $titolo;
	}

	function setOrdinamento($ordinamento) {
		$this->ordinamento = $ordinamento;
	}

	function setAsse($asse) {
		$this->asse = $asse;
	}

	function setAzioniAutoritaUrbana($azioni_autorita_urbana) {
		$this->azioni_autorita_urbana = $azioni_autorita_urbana;
	}


	
}
