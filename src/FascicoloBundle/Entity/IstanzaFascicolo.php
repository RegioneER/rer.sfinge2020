<?php

namespace FascicoloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RichiesteBundle\Entity\OggettoRichiesta;
use RichiesteBundle\Entity\Proponente;

/**
 * Description of IstanzaFascicolo
 *
 * @author aturdo
 * 
 * @ORM\Entity
 * @ORM\Table(name="fascicoli_istanze_fascicoli")
 * @ORM\Entity(repositoryClass="FascicoloBundle\Entity\IstanzaFascicoloRepository")
 */
class IstanzaFascicolo {
	
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
	 * @ORM\ManyToOne(targetEntity="Fascicolo")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	protected $fascicolo;
	
	/**
	 * @var IstanzaPagina $indice
	 * 
	 * @ORM\OneToOne(targetEntity="IstanzaPagina", inversedBy="istanzaFascicolo", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */	
	protected $indice;

	/**
	 * @ORM\OneToOne(targetEntity="RichiesteBundle\Entity\OggettoRichiesta", mappedBy="istanza_fascicolo")
	 */
	protected $oggetto_richiesta;

    /**
     * @ORM\OneToOne(targetEntity="RichiesteBundle\Entity\Proponente", mappedBy="istanza_fascicolo")
     */
    protected $proponente;

	
	public function __construct(?Fascicolo $fascicolo = null) {
		$this->fascicolo = $fascicolo;
	}
	
	public function getId() {
		return $this->id;
	}

	public function getFascicolo() {
		return $this->fascicolo;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setFascicolo($fascicolo) {
		$this->fascicolo = $fascicolo;
	}
	
	public function getIndice() {
		return $this->indice;
	}

	public function setIndice($indice) {
		$this->indice = $indice;
	}
	
	public function getByAlias($alias): ?IstanzaPagina {
		return $this->indice && $this->indice->getPagina()->getAlias() == $alias ? $this->indice : null;
	}

	public function __clone() {	
        if ($this->id) {
            $this->indice = clone $this->indice;
            $this->indice->setIstanzaFascicolo($this);      
        }     
    }

    /**
     * @param $oggettoRichiesta
     * @return IstanzaFascicolo
     */
    public function setOggettoRichiesta(OggettoRichiesta $oggettoRichiesta = null): self
    {
        $this->oggetto_richiesta = $oggettoRichiesta;

        return $this;
    }

    /**
     * @return OggettoRichiesta 
     */
    public function getOggettoRichiesta(): ?OggettoRichiesta
    {
        return $this->oggetto_richiesta;
    }

    /**
     * @return mixed
     */
    public function getProponente(): ?Proponente
    {
        return $this->proponente;
    }

    /**
     * @param mixed $proponente
     */
    public function setProponente($proponente): void
    {
        $this->proponente = $proponente;
    }
}
