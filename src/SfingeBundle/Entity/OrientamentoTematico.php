<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;

/**
 * OrientamentoTematico
 *
 * @ORM\Table(name="orientamenti_tematici")
 * @ORM\Entity()
 */
class OrientamentoTematico extends EntityTipo
{

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\SistemaProduttivo", inversedBy="orientamentiTematici")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $sistemaProduttivo;
	
	/**
	 * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\PrioritaTecnologica", mappedBy="orientamentoTematico")
	 */
	protected $prioritaTecnologiche;
	
	/**
     * @var boolean $laboratori
     * @ORM\Column(name="laboratori", type="boolean", nullable=true )
     */
    private $laboratori;
    

	function getSistemaProduttivo() {
		return $this->sistemaProduttivo;
	}

	function getPrioritaTecnologiche() {
		return $this->prioritaTecnologiche;
	}

	function setSistemaProduttivo($sistemaProduttivo) {
		$this->sistemaProduttivo = $sistemaProduttivo;
	}

	function setPrioritaTecnologiche($prioritaTecnologiche) {
		$this->prioritaTecnologiche = $prioritaTecnologiche;
	}
	
	public function getLaboratori() {
		return $this->laboratori;
	}

	public function setLaboratori($laboratori) {
		$this->laboratori = $laboratori;
	}

}
