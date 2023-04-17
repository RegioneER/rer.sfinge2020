<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\ModalitaFinanziamentoRepository")
 * @ORM\Table(name="modalita_finanziamenti",
 *  indexes={
 *      @ORM\Index(name="idx_procedura_modalita_finanziamento_id", columns={"procedura_id"}),
 *  })
 */
class ModalitaFinanziamento extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="modalita_finanziamento")
	 * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=false)
	 */
	protected $procedura;


	/**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\VoceModalitaFinanziamento", mappedBy="modalita_finanziamento")
	 */
	protected $voci_modalita_finanziamento;

	/**
	 * @ORM\Column(type="integer", name="ordinamento", nullable=false)
	 */
	protected $ordinamento;

	/**
	 * @ORM\Column(type="string", name="titolo", nullable=false)
	 */
	protected $titolo;

	/**
	 * @ORM\Column(type="string", length=25, nullable=false)
	 */
	protected $codice;

	
	public function __construct() {
		$this->voci_modalita_finanziamento = new ArrayCollection();
	}

	public function getId() {
		return $this->id;
	}

	public function getProcedura() {
		return $this->procedura;
	}

	public function getOrdinamento() {
		return $this->ordinamento;
	}

	public function getTitolo() {
		return $this->titolo;
	}

	public function getCodice() {
		return $this->codice;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setProcedura($procedura) {
		$this->procedura = $procedura;
	}

	public function setOrdinamento($ordinamento) {
		$this->ordinamento = $ordinamento;
	}

	public function setTitolo($titolo) {
		$this->titolo = $titolo;
	}

	public function setCodice($codice) {
		$this->codice = $codice;
	}

	public function getVociModalitaFinanziamento() {
		return $this->voci_modalita_finanziamento;
	}

	public function setVociModalitaFinanziamento($voci_modalita_finanziamento) {
		$this->voci_modalita_finanziamento = $voci_modalita_finanziamento;
	}

}
