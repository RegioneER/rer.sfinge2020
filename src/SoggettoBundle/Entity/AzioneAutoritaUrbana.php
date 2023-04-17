<?php

namespace SoggettoBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="SoggettoBundle\Entity\AzioneAutoritaUrbanaRepository")
 * @ORM\Table(name="azioni_autorita_urbane")
 */
class AzioneAutoritaUrbana extends EntityLoggabileCancellabile {

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
	 * @ORM\Column(type="string", length=1000,  name="link", nullable=false)
	 */
	protected $link;
	
	/**
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $ordinamento;	

	/**
	 * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\AutoritaUrbana", inversedBy="azioni_autorita_urbana")
	 * @ORM\JoinColumn(name="autorita_urbana_id", referencedColumnName="id", nullable=false)
	 */
	protected $autorita_urbana;
	
	/**
	 * @ORM\OneToMany(targetEntity="SoggettoBundle\Entity\AllegatiAzioniAutoritaUrbane", mappedBy="azione_autorita_urbana")
	 */
	protected $allegati;
	
	
    public function __construct() {
		$this->allegati = new \Doctrine\Common\Collections\ArrayCollection();
    }
	
	function getId() {
		return $this->id;
	}

	function getTitolo() {
		return $this->titolo;
	}

	function getLink() {
		return $this->link;
	}

	function getOrdinamento() {
		return $this->ordinamento;
	}

	function getAutoritaUrbana() {
		return $this->autorita_urbana;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setTitolo($titolo) {
		$this->titolo = $titolo;
	}

	function setLink($link) {
		$this->link = $link;
	}

	function setOrdinamento($ordinamento) {
		$this->ordinamento = $ordinamento;
	}

	function setAutoritaUrbana($autorita_urbana) {
		$this->autorita_urbana = $autorita_urbana;
	}

	function getAllegati() {
		return $this->allegati;
	}

	function setAllegati($allegati) {
		$this->allegati = $allegati;
	}
		
}
