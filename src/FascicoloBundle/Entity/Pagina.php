<?php

namespace FascicoloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Description of Pagina
 *
 * @author aturdo
 * 
 * @ORM\Table(name="fascicoli_pagine")
 * @ORM\Entity(repositoryClass="FascicoloBundle\Entity\PaginaRepository")
 */
class Pagina {

    /**
	 * @var integer $id
	 * 
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */	
	protected $id;
	
	/**
	 * @var Fascicolo $fascicolo
	 * 
	 * @ORM\OneToOne(targetEntity="Fascicolo", mappedBy="indice")
	 */
	protected $fascicolo;
	
	/**
     * @var ArrayCollection $frammenti
     * 
	 * @ORM\OneToMany(targetEntity="Frammento", mappedBy="pagina", cascade={"persist"})
	 * @ORM\OrderBy({"ordinamento" = "ASC"})
	 */
	protected $frammenti;
	
	/**
	 * @var string $titolo
	 * 
	 * @ORM\Column(name="titolo", type="string", length=255, nullable=false)
	 * @Assert\NotNull
	 * @Assert\Length(max=255, maxMessage="Campo limitato a 255 caratteri")
	 */
	protected $titolo;
	
	/**
     * @var Frammento $frammentoContenitore
     * 
	 * @ORM\ManyToOne(targetEntity="Frammento", inversedBy="sottoPagine")
	 * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
	 */
	protected $frammentoContenitore;
	
    /**
	 * @var integer $maxMolteplicita
	 * 
     * @ORM\Column(type="integer")
	 * @Assert\NotNull
     */	
	protected $maxMolteplicita;	
	
	/**
	 * @var integer $minMolteplicita
	 * 
     * @ORM\Column(type="integer")
	 * @Assert\NotNull
     */	
	protected $minMolteplicita;
	
	/**
	 * @var integer $ordinamento
	 * 
     * @ORM\Column(name="ordinamento",type="integer",nullable=false)
     */	
	protected $ordinamento;
	
	/**
	 * @var string $alias
	 * 
	 * @ORM\Column(name="alias", type="string", nullable=false, length=255)
	 *
	 * @Assert\Regex(pattern="/^[a-z0-9_]+$/", message="L'alias può contenere solo lettere minuscole, cifre ed underscore") 
	 * @Assert\NotNull
	 * @Assert\Length(max=255, maxMessage="Campo limitato a 255 caratteri")
	 */
	protected $alias;
	
	/**
	 * @var string $callback
	 * 
	 * @Assert\Length(max=255)
	 * @Assert\Regex(pattern="/^[a-zA-Z0-9_]+$/", message="La callback può contenere solo lettere, cifre ed underscore") 
	 * @ORM\Column(name="callback", type="string", length=255, nullable=true)
	 */
	protected $callback;
	
	/**
	 * @var string $callbackPresenza
	 * 
	 * @Assert\Length(max=255)
	 * @Assert\Regex(pattern="/^[a-zA-Z0-9_]+$/", message="La callback può contenere solo lettere, cifre ed underscore") 
	 * @ORM\Column(name="callbackPresenza", type="string", length=255, nullable=true)
	 */
	protected $callbackPresenza;
	
	protected $frammentiIndicizzati;
		
	public function __construct() {
		$this->frammenti = new ArrayCollection();
	}
	
	public function getId() {
		return $this->id;
	}

	public function getFascicolo($nested = false) {
		if (!$nested || !is_null($this->fascicolo)) {
			return $this->fascicolo;
		}
		
		return $this->getFrammentoContenitore()->getPagina()->getFascicolo($nested);		
	}

	public function getFrammenti() {
		return $this->frammenti;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setFascicolo($fascicolo) {
		$this->fascicolo = $fascicolo;
	}

	public function setFrammenti($frammenti) {
		$this->frammenti = $frammenti;
	}
	
	public function getTitolo() {
		return $this->titolo;
	}

	public function setTitolo($titolo) {
		$this->titolo = $titolo;
	}

	public function getFrammentoContenitore() {
		return $this->frammentoContenitore;
	}

	public function setFrammentoContenitore($frammentoContenitore) {
		$this->frammentoContenitore = $frammentoContenitore;
	}
	
	public function getMaxMolteplicita() {
		return $this->maxMolteplicita;
	}

	public function setMaxMolteplicita($maxMolteplicita) {
		$this->maxMolteplicita = $maxMolteplicita;
	}
	
	function getMinMolteplicita() {
		return $this->minMolteplicita;
	}

	function setMinMolteplicita($minMolteplicita) {
		$this->minMolteplicita = $minMolteplicita;
	}

	
	public function getCampiEvidenziati() {
		$campiEvidenziati = new ArrayCollection();
		foreach ($this->getFrammenti() as $frammento) {
			$campiEvidenziati->set($frammento->getAlias(), $frammento->getCampiEvidenziati());
		}
		
		return $campiEvidenziati;
	}
	
	function getOrdinamento() {
		return $this->ordinamento;
	}

	function setOrdinamento($ordinamento) {
		$this->ordinamento = $ordinamento;
	}

	function getAlias() {
		return $this->alias;
	}

	function setAlias($alias) {
		$this->alias = $alias;
	}
	
	public function getCallback() {
		return $this->callback;
	}

	public function setCallback($callback) {
		$this->callback = $callback;
	}
	
	public function getCallbackPresenza() {
		return $this->callbackPresenza;
	}

	public function setCallbackPresenza($callbackPresenza) {
		$this->callbackPresenza = $callbackPresenza;
	}
	
	public function getPath() {
		if (!is_null($this->fascicolo)) {
			return $this->getAlias();
		} else {
			return $this->getFrammentoContenitore()->getPath().".".$this->getAlias();
		}
	}
	
	public function getMessaggioMolteplicita() {
		$messaggio = null;
		if ($this->maxMolteplicita != 0 || $this->minMolteplicita != 0) {
			
			if ($this->minMolteplicita != 0) {
				$messaggio .= "Devi inserire almeno ".$this->minMolteplicita." ".($this->minMolteplicita == 1 ? "riga" : "righe");
				
				if ($this->maxMolteplicita != 0) {
					$messaggio .= " (";
				}				
			}
			
			if ($this->maxMolteplicita != 0) {
				$messaggio .= "Puoi inserire massimo ".$this->maxMolteplicita;
				
				if ($this->minMolteplicita != 0) {
					$messaggio .= ")";
				} else {
					$messaggio .= " ".($this->maxMolteplicita == 1 ? "riga" : "righe");
				}			
			}			
		}
		
		return $messaggio;
	}
	
	public function __clone() {
		if($this->id){
			$frammenti = array();
			foreach ($this->frammenti as $frammento) {
				$frammentoClonato = clone $frammento;
				$frammentoClonato->setPagina($this);
				$frammenti[] = $frammentoClonato;
			}
			$this->setFrammenti($frammenti);
		}
	}
	
	public function getByAlias($alias) {
		if (is_null($this->frammentiIndicizzati)) {
			$this->frammentiIndicizzati = new ArrayCollection();
			foreach ($this->getFrammenti() as $frammento) {
				$this->frammentiIndicizzati->set($frammento->getAlias(), $frammento);
			}
		}
		
		return $this->frammentiIndicizzati->get($alias);
	}
    
	public function getAllLeafPath() {
        $paths = array();
        foreach ($this->getFrammenti() as $frammento){
            $paths = array_merge($paths, $frammento->getAllLeafPath());
        }  
        
        return $paths;        
	}     

}
