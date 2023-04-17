<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="parametri_sistemi")
 */
class ParametroSistema extends EntityTipo
{
	/**
     * @var string $descrizione
     *
     * @ORM\Column(name="valore", type="string", length=1000)
     */
    protected $valore;

	function getValore() {
		return $this->valore;
	}

	function setValore($valore) {
		$this->valore = $valore;
	}

}
