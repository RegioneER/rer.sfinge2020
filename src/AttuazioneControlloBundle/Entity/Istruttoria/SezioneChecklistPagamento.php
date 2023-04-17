<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="sezioni_checklist_pagamenti")
 */
class SezioneChecklistPagamento
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
	
	/**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento", inversedBy="sezioni")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $checklist;
	
	/**
	 * @ORM\Column(type="text", nullable=false)
	 */
	protected $descrizione;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ElementoChecklistPagamento", mappedBy="sezione_checklist", cascade={"persist"})
	 * @var Collection|ElementoChecklistPagamento[]
	 */		
	protected $elementi;
	
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $ordinamento;

	/**
	 * @ORM\Column(type="boolean", name="commento", nullable=false)
	 */
	protected $commento;	
    
    /**
     * @ORM\Column(type="string", name="codice", nullable=true, length=50)
     * @var string|null
     */
    protected $codice;
	
	function __construct() {
		$this->elementi = new \Doctrine\Common\Collections\ArrayCollection();
	}
	
	function getId() {
		return $this->id;
	}

	function getChecklist() {
		return $this->checklist;
	}

	function getDescrizione() {
		return $this->descrizione;
	}

	/**
	 * @var Collection|ElementoChecklistPagamento[]
	 */
	function getElementi(): Collection {
		return $this->elementi;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setChecklist($checklist) {
		$this->checklist = $checklist;
	}

	function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;
	}

	function setElementi(Collection $elementi) {
		$this->elementi = $elementi;
	}

	function getOrdinamento() {
		return $this->ordinamento;
	}

	function setOrdinamento($ordinamento) {
		$this->ordinamento = $ordinamento;
	}
	
	function getCommento() {
		return $this->commento;
	}

	function setCommento($commento) {
		$this->commento = $commento;
	}

	public function addElementi(ElementoChecklistPagamento $elemento): self {
		$this->elementi[] = $elemento;
		return $this;
	}

	public function removeElementi(ElementoChecklistPagamento $elemento): void {
		$this->elementi->removeElement($elemento);
	}
    
    public function getCodice(): ?string {
        return $this->codice;
    }

    public function setCodice(?string $codice): self {
        $this->codice = $codice;

        return $this;
    }
}
