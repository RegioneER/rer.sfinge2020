<?php

namespace FascicoloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
USE Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of IstanzaFrammento
 *
 * @author aturdo
 * 
 * @ORM\Entity
 * @ORM\Table(name="fascicoli_istanze_frammenti")
 */
class IstanzaFrammento {

    /**
	 * @var integer $id
	 * 
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */	
	protected $id;
	
	/**
	 * @var Frammento $frammento
	 * 
	 * @ORM\ManyToOne(targetEntity="Frammento")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	protected $frammento;	
	
	/**
	 * @var IstanzaPagina $istanzaPagina
	 * 
	 * @ORM\ManyToOne(targetEntity="IstanzaPagina", inversedBy="istanzeFrammenti")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	protected $istanzaPagina;
	
	/**
     * @var ArrayCollection $istanzeCampi
     * 
	 * @ORM\OneToMany(targetEntity="IstanzaCampo", mappedBy="istanzaFrammento", cascade={"persist"}, orphanRemoval=true)
	 */
	protected $istanzeCampi;
	
	/**
     * @var ArrayCollection $istanzeSottoPagine
     * 
	 * @ORM\OneToMany(targetEntity="IstanzaPagina", mappedBy="istanzaFrammentoContenitore", cascade={"persist"})
	 */
	protected $istanzeSottoPagine;
	
	/**
     * @var ArrayCollection $istanzeCampiIndicizzate
	 */	
	protected $istanzeCampiIndicizzate;
	
	/**
     * @var ArrayCollection $istanzeSottoPagineIndicizzate
	 */	
	protected $istanzeSottoPagineIndicizzate;	
	
	public function __construct() {
		$this->istanzeCampi = new \Doctrine\Common\Collections\ArrayCollection();
		$this->istanzeSottoPagine = new \Doctrine\Common\Collections\ArrayCollection();
		$this->istanzeCampiIndicizzate = null;
		$this->istanzeSottoPagineIndicizzate = null;
	}
	
	public function getId() {
		return $this->id;
	}

	public function getFrammento() {
		return $this->frammento;
	}

	public function getIstanzaPagina() {
		return $this->istanzaPagina;
	}

	public function getIstanzeCampi() {
		return $this->istanzeCampi;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setFrammento(Frammento $frammento) {
		$this->frammento = $frammento;
	}

	public function setIstanzaPagina($istanzaPagina) {
		$this->istanzaPagina = $istanzaPagina;
	}

	public function setIstanzeCampi($istanzeCampi) {
		$this->istanzeCampi = $istanzeCampi;
	}

	public function aggiungiIstanzaCampo($istanzaCampo) {
		$this->istanzeCampi->add($istanzaCampo);
		$istanzaCampo->setIstanzaFrammento($this);
	}
	
	public function aggiungiIstanzaSottoPagina($istanzaPagina) {
		$this->istanzeSottoPagine->add($istanzaPagina);
		if (!is_null($this->istanzeSottoPagineIndicizzate)) {
			if (!$this->istanzeSottoPagineIndicizzate->containsKey($istanzaPagina->getPagina()->getAlias())) {
				$istanze = new \Doctrine\Common\Collections\ArrayCollection();
				$this->istanzeSottoPagineIndicizzate->set($istanzaPagina->getPagina()->getAlias(), $istanze);	
			}

			$this->istanzeSottoPagineIndicizzate->get($istanzaPagina->getPagina()->getAlias())->add($istanzaPagina);
		}
	}	
	
	public function rimuoviIstanzaCampo($istanzaCampo) {
		$this->istanzeCampi->removeElement($istanzaCampo);
		$istanzaCampo->setIstanzaFrammento(null);
	}	
	
	public function getIstanzeSottoPagine() {
		return $this->istanzeSottoPagine;
	}

	public function setIstanzeSottoPagine($istanzeSottoPagine) {
		$this->istanzeSottoPagine = $istanzeSottoPagine;
	}
	
	protected function indicizzaIstanzeCampi() {
		if (is_null($this->istanzeCampiIndicizzate)) {
			$this->istanzeCampiIndicizzate = new \Doctrine\Common\Collections\ArrayCollection();
			foreach ($this->getIstanzeCampi() as $istanzaCampo) {				
				if (!$this->istanzeCampiIndicizzate->containsKey($istanzaCampo->getCampo()->getAlias())) {
					$istanze = new \Doctrine\Common\Collections\ArrayCollection();
					$this->istanzeCampiIndicizzate->set($istanzaCampo->getCampo()->getAlias(), $istanze);	
				}
				
				$this->istanzeCampiIndicizzate->get($istanzaCampo->getCampo()->getAlias())->add($istanzaCampo);
			}
		}		
	}
	
	public function getIstanzeCampiIndicizzate() {
		$this->indicizzaIstanzeCampi();
		
		return $this->istanzeCampiIndicizzate;
	}

	public function getIstanzeCampiByAlias($alias) {
		$this->indicizzaIstanzeCampi();
		
		return $this->istanzeCampiIndicizzate->containsKey($alias) ? $this->istanzeCampiIndicizzate->get($alias) : new \Doctrine\Common\Collections\ArrayCollection();
	}
	
	public function getIstanzeSottoPagineByAlias($alias) {
		if (is_null($this->istanzeSottoPagineIndicizzate)) {
			$this->istanzeSottoPagineIndicizzate = new \Doctrine\Common\Collections\ArrayCollection();
			foreach ($this->getIstanzeSottoPagine() as $istanzaPagina) {
				if (!$this->istanzeSottoPagineIndicizzate->containsKey($istanzaPagina->getPagina()->getAlias())) {
					$istanze = new \Doctrine\Common\Collections\ArrayCollection();
					$this->istanzeSottoPagineIndicizzate->set($istanzaPagina->getPagina()->getAlias(), $istanze);	
				}
				
				$this->istanzeSottoPagineIndicizzate->get($istanzaPagina->getPagina()->getAlias())->add($istanzaPagina);
			}
		}
		
		return $this->istanzeSottoPagineIndicizzate->containsKey($alias) ? $this->istanzeSottoPagineIndicizzate->get($alias) : new \Doctrine\Common\Collections\ArrayCollection();
	}
	
	public function getValoreRawByAlias($alias) {
		$istanzeSottoCampi = $this->getIstanzeCampiByAlias($alias);
		$valori = array();
		
		foreach ($istanzeSottoCampi as $istanzaSottoCampo) {
                        if ($istanzaSottoCampo->getCampo()->getTipoCampo()->getCodice() == "checkbox") {
                            $valori[] = $istanzaSottoCampo->getValoreRaw() == "1" ? "&check;" : "";
                        } else {
                            $valori[] = $istanzaSottoCampo->getValoreRaw();
                        }
		}
		
		return implode(" / ", $valori);
	}
	
	public function getByAlias($alias) {
		$elementi = $this->getIstanzeSottoPagineByAlias($alias);
		
		if ($elementi->count() > 0) { 
			return $elementi; 
		}
		
		return $this->getIstanzeCampiByAlias($alias);
	}
	
	public function isEmpty() {
		return $this->istanzeCampi->count() == 0 && $this->istanzeSottoPagine->count() == 0;
	}
	
	public function __clone() {	
        if ($this->id) {          
            if (!is_null($this->istanzeCampi)) {
                $istanze_campi = array();
                foreach ($this->istanzeCampi as $istanza_campo) {
                    $istanza_campo_clonato = clone $istanza_campo;
                    $istanza_campo_clonato->setIstanzaFrammento($this);
                    $istanze_campi[] = $istanza_campo_clonato;
                }
                $this->setIstanzeCampi($istanze_campi);
            }   
            
            if (!is_null($this->istanzeSottoPagine)) {
                $istanze_pagine = array();
                foreach ($this->istanzeSottoPagine as $istanza_pagina) {
                    $istanza_pagina_clonata = clone $istanza_pagina;
                    $istanza_pagina_clonata->setIstanzaFrammentoContenitore($this);
                    $istanze_pagine[] = $istanza_pagina_clonata;
                }
                $this->setIstanzeSottoPagine($istanze_pagine);
            }               
        }
    }      
}
