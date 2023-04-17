<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 28/01/16
 * Time: 15:26
 */

namespace RichiesteBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityTipo;

/**
 * @ORM\Table(name="tipi_mandatari")
 * @ORM\Entity()
 */
class TipoMandatario extends EntityTipo
{
	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura")
	 * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=true)
	 */
	private $procedura;
	
	public function getProcedura() {
		return $this->procedura;
	}

	public function setProcedura($procedura) {
		$this->procedura = $procedura;
	}

	public function __toString() {
		return $this->getDescrizione();
	}
}