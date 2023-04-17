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
 * @ORM\Table(name="fascicoli_vincoli")
 */
class Vincolo {
	
	 /**
	 * @var integer $id
	 * 
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */	
	protected $id;
	
	/**
	 * @var TipoVincolo $tipoVincolo
	 * 
	 * @ORM\ManyToOne(targetEntity="TipoVincolo")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $tipoVincolo;
	
	/**
	 * @var Campo $campo
	 * 
	 * @ORM\ManyToOne(targetEntity="Campo", inversedBy="vincoli")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	protected $campo;
	
	/**
	 * @var array $parametri
	 * 
	 * @ORM\Column(name="parametri", type="array", nullable=true)
	 */
	protected $parametri;
	
	function getId() {
		return $this->id;
	}

	function getTipoVincolo() {
		return $this->tipoVincolo;
	}

	function getCampo() {
		return $this->campo;
	}

	function getParametri() {
		return $this->parametri;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setTipoVincolo(TipoVincolo $tipoVincolo) {
		$this->tipoVincolo = $tipoVincolo;
	}

	function setCampo(Campo $campo) {
		$this->campo = $campo;
	}

	function setParametri($parametri) {
		$this->parametri = $parametri;
	}
	
	function __toString() {
		$result = "";
		foreach ($this->getParametri() as $chiave => $parametro) {
			if (!is_null($parametro)) {
				$result .= "$chiave: $parametro; ";
			}
		}
		
		return $result;
	}

}
