<?php

namespace FascicoloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of TipoCampo
 *
 * @author aturdo
 * 
 * @ORM\Entity
 * @ORM\Table(name="fascicoli_tipi_campi")
 */
class TipoCampo {

    /**
	 * @var integer $id
	 * 
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */	
	protected $id;

	/**
	 * @var string $nome
	 * 
	 * @ORM\Column(name="nome", type="string", length=255, nullable=false)
	 */
	protected $nome;
	
	/**
	 * @var string $codice
	 * 
	 * @ORM\Column(name="codice", type="string", length=255, nullable=false)
	 */
	protected $codice;
	
	/**
	 * @ORM\ManyToMany(targetEntity="TipoVincolo", mappedBy="tipiCampi")
     */
	protected $tipiVincoli;
	
	public function getId() {
		return $this->id;
	}

	public function getCodice() {
		return $this->codice;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setCodice($codice) {
		$this->codice = $codice;
	}

	public function getNome() {
		return $this->nome;
	}

	public function setNome($nome) {
		$this->nome = $nome;
	}
	
	public function getTipiVincoli() {
		return $this->tipiVincoli;
	}

	public function setTipiVincoli($tipiVincoli) {
		$this->tipiVincoli = $tipiVincoli;
	}
	
	public function __toString() {
		return $this->getNome();
	}
}
