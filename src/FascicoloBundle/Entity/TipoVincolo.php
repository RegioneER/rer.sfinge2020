<?php

namespace FascicoloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of TipoFrammento
 *
 * @author abuffa
 *
 * @ORM\Entity
 * @ORM\Table(name="fascicoli_tipi_vincoli")
 */
class TipoVincolo {
	
	 /**
	 * @var integer $id
	 * 
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */	
	protected $id;
	
	/**
	 * @var string $codice
	 * 
	 * @ORM\Column(name="codice", type="string", length=255, nullable=false)
	 */
	protected $codice;
	
	/**
	 * @var string $descrizione
	 * 
	 * @ORM\Column(name="descrizione", type="string", length=255, nullable=false)
	 */
	protected $descrizione;
	
	/**
     *
     * @ORM\ManyToMany(targetEntity="TipoCampo", inversedBy="tipiVincoli")
	 * @ORM\JoinTable(name="fascicoli_compatibilita_vincoli_campi")
     */
	protected $tipiCampi;
	
	public function __construct() {

	}
	
	public function getId() {
		return $this->id;
	}

	public function getCodice() {
		return $this->codice;
	}

	public function getDescrizione() {
		return $this->descrizione;
	}

	public function getTipiCampi() {
		return $this->tipiCampi;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setCodice($codice) {
		$this->codice = $codice;
	}

	public function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;
	}

	public function setTipiCampi($tipiCampi) {
		$this->tipiCampi = $tipiCampi;
	}

	public function __toString() {
		return $this->descrizione;
	}
	
}
