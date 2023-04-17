<?php

namespace FascicoloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Description of Frammento
 *
 * @author aturdo
 * 
 * @ORM\Table(name="fascicoli_frammenti")
 * @ORM\Entity(repositoryClass="FascicoloBundle\Entity\FrammentoRepository")
 */
class Frammento {

    /**
	 * @var integer $id
	 * 
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */	
	protected $id;
	
	/**
	 * @var Pagina $pagina
	 * 
	 * @ORM\ManyToOne(targetEntity="Pagina", inversedBy="frammenti")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	protected $pagina;
	
	/**
     * @var Collection $campi
     * 
	 * @ORM\OneToMany(targetEntity="Campo", mappedBy="frammento" , cascade={"persist"})
	 * @ORM\OrderBy({"ordinamento" = "ASC"})
	 */
	protected $campi;
	
	/**
	 * @var Pagina $frammento
	 * 
	 * @ORM\ManyToOne(targetEntity="TipoFrammento", inversedBy="frammenti")
	 * @ORM\JoinColumn(nullable=false)
	 * @Assert\NotNull
	 */
	protected $tipoFrammento;
	
	/**
	 * @var string $titolo
	 * 
	 * @ORM\Column(name="titolo", type="string", length=255, nullable=true)
	 * @Assert\Length(max=255, maxMessage="Campo limitato a 255 caratteri")
	 */
	protected $titolo;
	
	/**
     * @var Collection $sottoPagine
     * 
	 * @ORM\OneToMany(targetEntity="Pagina", mappedBy="frammentoContenitore", cascade={"persist"})
	 * @ORM\OrderBy({"ordinamento" = "ASC"})
	 */
	protected $sottoPagine;	
	
	/**
	 * @var string $action
	 * 
	 * @ORM\Column(name="action", type="string", length=255, nullable=true)
	 */
	protected $action;	
	
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
	 * @var string $callbackPresenza
	 * 
	 * @Assert\Length(max=255)
	 * @Assert\Regex(pattern="/^[a-zA-Z0-9_]+$/", message="La callback può contenere solo lettere, cifre ed underscore") 
	 * @ORM\Column(name="callbackPresenza", type="string", length=255, nullable=true)
	 */
	protected $callbackPresenza;
	
	/**
     * @var Collection $campiIndicizzati
	 */	
	protected $campiIndicizzati;
	
	/**
     * @var Collection $sottoPagineIndicizzate
	 */	
	protected $sottoPagineIndicizzate;	
	
	/**
	 * @ORM\Column(name="nota", type="text", nullable=true)
	 */
	protected $nota;
	
	public function __construct() {
		$this->campi = new ArrayCollection();
		$this->sottoPagine = new ArrayCollection();
	}
	
	public function getId() {
		return $this->id;
	}

	public function getPagina() {
		return $this->pagina;
	}

	public function getCampi() {
		return $this->campi;
	}

	public function getTipoFrammento() {
		return $this->tipoFrammento;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setPagina($pagina) {
		$this->pagina = $pagina;
	}

	public function setCampi($campi) {
		$this->campi = $campi;
	}

	public function setTipoFrammento($tipoFrammento) {
		$this->tipoFrammento = $tipoFrammento;
	}

	public function getTitolo() {
		return $this->titolo;
	}

	public function setTitolo($titolo) {
		$this->titolo = $titolo;
	}
	
	public function getSottoPagine() {
		return $this->sottoPagine;
	}

	public function setSottoPagine($sottoPagine) {
		$this->sottoPagine = $sottoPagine;
	}
	
	public function getCampiEvidenziati() {
		$campiEvidenziati = new ArrayCollection();
		foreach ($this->getCampi() as $campo) {
			if ($campo->getEvidenziato()) {
				$campiEvidenziati->add($campo);
			}
		}
		
		return $campiEvidenziati;
	}
	
	public function getAction() {
		return $this->action;
	}

	public function setAction($action) {
		$this->action = $action;
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
	
	public function getCallbackPresenza() {
		return $this->callbackPresenza;
	}

	public function setCallbackPresenza($callbackPresenza) {
		$this->callbackPresenza = $callbackPresenza;
	}	

	public function addSottoPagina($sotto_pagina) {
        $this->getSottoPagine()->add($sotto_pagina);
		$sotto_pagina->setFrammentoContenitore($this);
    }
	
	public function getPath() {
		return $this->getPagina()->getPath().".".$this->getAlias();
	}	
	
	public function __toString() {
		if ($this->getTitolo()) {
			return $this->getTitolo();
		} else {
			return "".$this->getId();
		}
	}
	
	public function __clone() {
		if($this->id){
			$campi = array();
			foreach ($this->campi as $campo) {
				$campoClonato = clone $campo;
				$campoClonato->setFrammento($this);
				$campi[] = $campoClonato;
			}
			$this->setCampi($campi);
			
			$sottoPagine = array();
			foreach ($this->sottoPagine as $sottoPagina) {
				$sottoPaginaClonata = clone $sottoPagina;
				$sottoPaginaClonata->setFrammentoContenitore($this);
				$sottoPagine[] = $sottoPaginaClonata;
			}
			$this->setSottoPagine($sottoPagine);
		}
	}
	
	protected function indicizzaCampi() {
		if (is_null($this->campiIndicizzati)) {
			$this->campiIndicizzati = new ArrayCollection();
			foreach ($this->getCampi() as $campo) {				
				$this->campiIndicizzati->set($campo->getAlias(), $campo);	
			}
		}		
	}	
	
	public function getCampiByAlias($alias) {
		$this->indicizzaCampi();
		
		return $this->campiIndicizzati->containsKey($alias) ? $this->campiIndicizzati->get($alias) : null;
	}
	
	public function getSottoPagineByAlias($alias) {
		if (is_null($this->sottoPagineIndicizzate)) {
			$this->sottoPagineIndicizzate = new ArrayCollection();
			foreach ($this->getSottoPagine() as $pagina) {		
				$this->sottoPagineIndicizzate->set($pagina->getAlias(), $pagina);
			}
		}
		
		return $this->sottoPagineIndicizzate->containsKey($alias) ? $this->sottoPagineIndicizzate->get($alias) : null;
	}	

	public function getByAlias($alias) {
		$elemento = $this->getSottoPagineByAlias($alias);
		
		if (!is_null($elemento)) { 
			return $elemento; 
		}
		
		return $this->getCampiByAlias($alias);
	}
    
	public function getAllLeafPath() {
        $paths = array();
        foreach ($this->getCampi() as $campo){
            $paths[] = $campo->getPath();
        } 
        
        foreach ($this->getSottoPagine() as $sotto_pagina){
            $paths = array_merge($paths, $sotto_pagina->getAllLeafPath());
        }
        
        return $paths;
	}     
	
	public function getNota() {
		return $this->nota;
	}

	public function setNota($nota) {
		$this->nota = $nota;
	}

}
