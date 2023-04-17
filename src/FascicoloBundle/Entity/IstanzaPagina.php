<?php

namespace FascicoloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of IstanzaPagina
 *
 * @author aturdo
 * 
 * @ORM\Entity
 * @ORM\Table(name="fascicoli_istanze_pagine")
 */
class IstanzaPagina {

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
	 * @ORM\ManyToOne(targetEntity="Pagina")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	protected $pagina;	
	
	/**
	 * @var IstanzaFascicolo $istanzaFascicolo
	 * 
	 * @ORM\OneToOne(targetEntity="IstanzaFascicolo", mappedBy="indice")
	 */
	protected $istanzaFascicolo;
	
	/**
     * @var ArrayCollection $istanzeFrammenti
     * 
	 * @ORM\OneToMany(targetEntity="IstanzaFrammento", mappedBy="istanzaPagina", cascade={"persist"})
	 */
	protected $istanzeFrammenti;
	
	/**
     * @var IstanzaFrammento $istanzaFrammentoContenitore
     * 
	 * @ORM\ManyToOne(targetEntity="IstanzaFrammento", inversedBy="istanzeSottoPagine")
	 * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
	 */
	protected $istanzaFrammentoContenitore;
	
	/**
     * @var ArrayCollection $istanzeCampiIndicizzate
	 */	
	protected $istanzeFrammentiIndicizzate;	
	
	public function __construct(?Pagina $pagina = null) {
		$this->istanzeFrammenti = new ArrayCollection();
		$this->istanzeFrammentiIndicizzate = null;
		$this->pagina = $pagina;
	}
	
	public function getId() {
		return $this->id;
	}

	public function getPagina() {
		return $this->pagina;
	}

	/**
	 * @return IstanzaFascicolo
	 */
	public function getIstanzaFascicolo() {
		if (!is_null($this->istanzaFascicolo)) {
			return $this->istanzaFascicolo;
		}
		
		return $this->getIstanzaFrammentoContenitore()->getIstanzaPagina()->getIstanzaFascicolo();
	}

	public function getIstanzeFrammenti() {
		return $this->istanzeFrammenti;
	}
    
	public function getIstanzeFrammentiByAlias($alias) {
		if (is_null($this->istanzeFrammentiIndicizzate)) {
			$this->istanzeFrammentiIndicizzate = new ArrayCollection();
			foreach ($this->getIstanzeFrammenti() as $istanzaFrammento) {
				if (!$this->istanzeFrammentiIndicizzate->containsKey($istanzaFrammento->getFrammento()->getAlias())) {
					$istanze = new ArrayCollection();
					$this->istanzeFrammentiIndicizzate->set($istanzaFrammento->getFrammento()->getAlias(), $istanze);	
				}
				
				$this->istanzeFrammentiIndicizzate->get($istanzaFrammento->getFrammento()->getAlias())->add($istanzaFrammento);
			}
		}
		
		return $this->istanzeFrammentiIndicizzate->containsKey($alias) ? $this->istanzeFrammentiIndicizzate->get($alias) : new ArrayCollection();
	}    

	public function setId($id) {
		$this->id = $id;
	}

	public function setPagina(Pagina $pagina) {
		$this->pagina = $pagina;
	}

	public function setIstanzaFascicolo($istanzaFascicolo) {
		$this->istanzaFascicolo = $istanzaFascicolo;
	}

	public function setIstanzeFrammenti($istanzeFrammenti) {
		$this->istanzeFrammenti = $istanzeFrammenti;
	}
	
	public function aggiungiIstanzaFrammento($istanzaFrammento) {
		$this->istanzeFrammenti->add($istanzaFrammento);
		$istanzaFrammento->setIstanzaPagina($this);
	}
	
	/**
	 * @return IstanzaFrammento
	 */
	public function getIstanzaFrammentoContenitore() {
		return $this->istanzaFrammentoContenitore;
	}

	public function setIstanzaFrammentoContenitore($istanzaFrammentoContenitore) {
		$this->istanzaFrammentoContenitore = $istanzaFrammentoContenitore;
	}
	
	public function getIstanzaPaginaContenitore() {
		$istanzaFrammento = $this->getIstanzaFrammentoContenitore();
		
		if (is_null($istanzaFrammento)) { return null; }
		
		return $istanzaFrammento->getIstanzaPagina();
	}
	
	public function isEmpty() {
		$conteggio = $this->istanzeFrammenti->count();
		if ($conteggio == 0) {
			return true;
		}
		
		foreach ($this->istanzeFrammenti as $istanzaFrammento) {
			if (!$istanzaFrammento->isEmpty()) {
				return false;
			}
		}
		
		return true;
	}
	
	public function getByAlias($alias): ?IstanzaFrammento {
		if (is_null($this->istanzeFrammentiIndicizzate)) {
			$this->istanzeFrammentiIndicizzate = new ArrayCollection();
			foreach ($this->istanzeFrammenti as $istanzaFrammento) {
				$this->istanzeFrammentiIndicizzate->set($istanzaFrammento->getFrammento()->getAlias(), $istanzaFrammento);
			}
		}
		
		return $this->istanzeFrammentiIndicizzate->get($alias);
	}
    
	public function __clone() {	
        if ($this->id) {         
            if (!is_null($this->istanzeFrammenti)) {
                $istanze_frammenti = array();
                foreach ($this->istanzeFrammenti as $istanza_frammento) {
                    $istanza_frammento_clonato = clone $istanza_frammento;
                    $istanza_frammento_clonato->setIstanzaPagina($this);
                    $istanze_frammenti[] = $istanza_frammento_clonato;
                }
                $this->setIstanzeFrammenti($istanze_frammenti);
            }            
        }
    }    
	
}
