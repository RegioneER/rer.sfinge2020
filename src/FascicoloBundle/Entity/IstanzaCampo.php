<?php

namespace FascicoloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of IstanzaCampo
 *
 * @author aturdo
 * 
 * @ORM\Entity
 * @ORM\Table(name="fascicoli_istanze_campi")
 */
class IstanzaCampo extends \BaseBundle\Entity\EntityLoggabileCancellabile {

    /**
	 * @var integer $id
	 * 
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */	
	protected $id;
	
	/**
	 * @var IstanzaFrammento $istanzaFrammento
	 * 
	 * @ORM\ManyToOne(targetEntity="IstanzaFrammento", inversedBy="istanzeCampi")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	protected $istanzaFrammento;
	
	/**
	 * @var Campo $campo
	 * 
	 * @ORM\ManyToOne(targetEntity="Campo")
	 * @ORM\JoinColumn(nullable=false, onDelete="RESTRICT")
	 */
	protected $campo;
	
	/**
	 * @var string $valore
	 * 
	 * @ORM\Column(name="valore", type="text")
	 */
	protected $valore;
	
	/**
	 * @var string $valoreRow
	 * 
	 * @ORM\Column(name="valoreRaw", type="text")
	 */
	protected $valoreRaw;
	
	public function getId() {
		return $this->id;
	}

	public function getIstanzaFrammento() {
		return $this->istanzaFrammento;
	}

	public function getCampo() {
		return $this->campo;
	}

	public function getValore() {
		return $this->valore;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setIstanzaFrammento($istanzaFrammento) {
		$this->istanzaFrammento = $istanzaFrammento;
	}

	public function setCampo(Campo $campo) {
		$this->campo = $campo;
	}

	public function setValore($valore) {
		$this->valore = $valore;
	}
	
	function getValoreRaw() {
		return $this->valoreRaw;
	}

	function setValoreRaw($valoreRaw) {
		$this->valoreRaw = $valoreRaw;
	}

}
