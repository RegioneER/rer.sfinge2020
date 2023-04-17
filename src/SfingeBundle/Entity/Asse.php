<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;

/**
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\AsseRepository")
 * @ORM\Table(name="assi")
 */
class Asse extends EntityTipo {
    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="titolo")
     * @var string
     */
    protected $titolo;

    /**
     * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\PermessiAsse", mappedBy="asse")
     * @var Collection|PermessiAsse[]
     */
    protected $permessi;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC36LivelloGerarchico", inversedBy="assi")
     * @ORM\JoinColumn(name="livello_gerachico_id", referencedColumnName="id", nullable=true)
     * @var TC36LivelloGerarchico
     */
    protected $livello_gerarchico;

    /**
     *  @ORM\OneToMany(targetEntity="SfingeBundle\Entity\Procedura", mappedBy="asse")
     */
    protected $procedure;
    
    /**
     *  @ORM\OneToMany(targetEntity="SoggettoBundle\Entity\AutoritaUrbana", mappedBy="asse")
     */
    protected $autorita_urbana;
	
    /**
     * @return string
     */
    public function getTitolo() {
        return $this->titolo;
    }

    /**
     * @param string $titolo
     * @return self
     */
    public function setTitolo($titolo) {
        $this->titolo = $titolo;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getTitolo() . ': ' . $this->getDescrizione();
    }

    /**
     * @return self
     */
    public function getAsse() {
        return $this;
    }

    public function __construct() {
        $this->permessi = new \Doctrine\Common\Collections\ArrayCollection();
        $this->procedure = new \Doctrine\Common\Collections\ArrayCollection();
		$this->autorita_urbana = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param PermessiAsse $permessi
     * @return Asse
     */
    public function addPermessi(PermessiAsse $permessi) {
        $this->permessi[] = $permessi;

        return $this;
    }

    /**
     * @param PermessiAsse $permessi
     */
    public function removePermessi(PermessiAsse $permessi) {
        $this->permessi->removeElement($permessi);
    }

    /**
     * @return Collection|PermessiAsse[]
     */
    public function getPermessi() {
        return $this->permessi;
    }

    /**
     * @param TC36LivelloGerarchico $livelloGerarchico
     * @return Asse
     */
    public function setLivelloGerarchico(TC36LivelloGerarchico $livelloGerarchico = null) {
        $this->livello_gerarchico = $livelloGerarchico;

        return $this;
    }

    /**
     * @return TC36LivelloGerarchico
     */
    public function getLivelloGerarchico() {
        return $this->livello_gerarchico;
    }
    
    function getProcedure() {
        return $this->procedure;
    }

    function setProcedure($procedure) {
        $this->procedure = $procedure;
    }

	function getAutoritaUrbane() {
		$iterator = $this->autorita_urbana->getIterator();
		$iterator->uasort(function ($a, $b) {
			return ($a->getOrdinamento() < $b->getOrdinamento()) ? -1 : 1;
		});
		return new \Doctrine\Common\Collections\ArrayCollection(iterator_to_array($iterator));		 
	}

	function setAutoritaUrbane($autorita_urbana) {
		$this->autorita_urbana = $autorita_urbana;
	}

	
}
