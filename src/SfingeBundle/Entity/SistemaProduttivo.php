<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;

/**
 * SistemaProduttivo
 *
 * @ORM\Table(name="sistemi_produttivi")
 * @ORM\Entity()
 */
class SistemaProduttivo extends EntityTipo
{

	/**
	 * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\OrientamentoTematico", mappedBy="sistemaProduttivo")
	 */
	protected $orientamentiTematici;
	
	/**
     * @var boolean $laboratori
     * @ORM\Column(name="laboratori", type="boolean", nullable=true )
     */
    private $laboratori;
    
	
	function getOrientamentiTematici() {
		return $this->orientamentiTematici;
	}

	function setOrientamentiTematici($orientamentiTematici) {
		$this->orientamentiTematici = $orientamentiTematici;
	}
	
	function __toString() {
		return $this->getCodice()." - ".$this->getDescrizione();
	}
	public function getLaboratori() {
		return $this->laboratori;
	}

	public function setLaboratori($laboratori) {
		$this->laboratori = $laboratori;
	}

}
