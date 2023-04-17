<?php

namespace RichiesteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use SfingeBundle\Entity\SistemaProduttivo;
use BaseBundle\Validator\Constraints\ValidaLunghezza;
use SfingeBundle\Entity\OrientamentoTematico;


/**
 * @ORM\Entity()
 * @ORM\Table(name="priorita_proponenti")
 */
class PrioritaProponente {
	
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
	
    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente", inversedBy="priorita")
     * @ORM\JoinColumn(nullable=false)
	 * 
     */
    private $proponente;	

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\SistemaProduttivo")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $sistema_produttivo;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\OrientamentoTematico")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $orientamento_tematico;

	/**
	 * @ORM\ManyToMany(targetEntity="SfingeBundle\Entity\PrioritaTecnologica")
	 * @ORM\JoinTable(name="priorita_tecnologiche_proponenti")
	 */
	protected $priorita_tecnologiche;
	
	/**
	 * @ORM\Column(name="coerenza_obiettivi", type="text", nullable=true)
	 * @ValidaLunghezza(min=5, max=2000, groups={"bando71"})
	 *
	 */
	private $coerenza_obiettivi;
	
	
	function getId() {
		return $this->id;
	}
	

	function getProponente():?Proponente {
		return $this->proponente;
	}

	function getSistemaProduttivo(): ?SistemaProduttivo {
		return $this->sistema_produttivo;
	}

	function getOrientamentoTematico(): ?OrientamentoTematico {
		return $this->orientamento_tematico;
	}

	function getPrioritaTecnologiche() {
		return $this->priorita_tecnologiche;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setProponente($proponente) {
		$this->proponente = $proponente;
	}

	function setSistemaProduttivo($sistema_produttivo) {
		$this->sistema_produttivo = $sistema_produttivo;
	}

	function setOrientamentoTematico($orientamento_tematico) {
		$this->orientamento_tematico = $orientamento_tematico;
	}

	function setPrioritaTecnologiche($priorita_tecnologiche) {
		$this->priorita_tecnologiche = $priorita_tecnologiche;
	}
	
	public function getCoerenzaObiettivi() {
		return $this->coerenza_obiettivi;
	}

	public function setCoerenzaObiettivi($coerenza_obiettivi) {
		$this->coerenza_obiettivi = $coerenza_obiettivi;
	}

	

}
