<?php

namespace SoggettoBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="dimensioni_imprese")
 */
class DimensioneImpresa extends \BaseBundle\Entity\EntityTipo
{

	function __toString(){
		return $this->getDescrizione();
	}

}