<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;

/**
 * PrioritaTecnologica
 *
 * @ORM\Table(name="priorita_tecnologiche")
 * @ORM\Entity()
 */
class PrioritaTecnologica extends EntityTipo
{

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\OrientamentoTematico", inversedBy="prioritaTecnologiche")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $orientamentoTematico;
	
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $descrizioneEstesa;

	function getOrientamentoTematico() {
		return $this->orientamentoTematico;
	}

	function getDescrizioneEstesa() {
		return $this->descrizioneEstesa;
	}

	function setOrientamentoTematico($orientamentoTematico) {
		$this->orientamentoTematico = $orientamentoTematico;
	}

	function setDescrizioneEstesa($descrizioneEstesa) {
		$this->descrizioneEstesa = $descrizioneEstesa;
	}
	
	function __toString() {
		return $this->getDescrizione();
	}

}
