<?php

namespace RichiesteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityTipo;

/**
 * TipologiaRisorsa
 *
 * @ORM\Table(name="tipologia_risorsa")
 * @ORM\Entity()
 */
class TipologiaRisorsa extends EntityTipo {

	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura")
	 * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=false)
	 */
	protected $procedura;

}
