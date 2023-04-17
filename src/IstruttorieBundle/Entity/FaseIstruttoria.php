<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use SfingeBundle\Entity\Procedura;

/**
 * FaseIstruttoria
 *
 * @ORM\Table(name="istruttorie_fasi")
 * @ORM\Entity(repositoryClass="IstruttorieBundle\Entity\FaseIstruttoriaRepository")
 * 
 * @author aturdo <aturdo@schema31.it>
 */
class FaseIstruttoria
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
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="fasi_istruttoria")
	 * @ORM\JoinColumn(nullable=false)
	 * @var Procedura
	 */
	protected $procedura;

	/**
	 * @ORM\Column(type="integer", nullable=false)
	 * @var int
	 */
	protected $step;	
	
	/**
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\ChecklistIstruttoria", mappedBy="fase", cascade={"persist"})
	 * @var Collection|ChecklistIstruttoria[]
	 */		
	protected $checklist;
	
	function __construct() {
		$this->checklist = new ArrayCollection();
	}
	
	
	function getId(): ?int {
		return $this->id;
	}

	function getProcedura(): ?Procedura {
		return $this->procedura;
	}

	function getStep(): ?int {
		return $this->step;
	}


	function setId(int $id): self {
		$this->id = $id;

		return $this;
	}

	function setProcedura(Procedura $procedura): self {
		$this->procedura = $procedura;

		return $this;
	}

	function setStep(int $step) {
		$this->step = $step;
	}

	/**
	 * @return Collection|ChecklistIstruttoria[]
	 */
	function getChecklist(): Collection {
		return $this->checklist;
	}

	function setChecklist(Collection $checklist) {
		$this->checklist = $checklist;
	}

}
