<?php

namespace AttuazioneControlloBundle\Entity\Revoche;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityTipo;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="tipi_fase_recuperi")
 */
class TipoFaseRecupero extends EntityTipo
{
	const COMPLETO = 'COMPLETO';
	const CORSO = 'CORSO';
	const MANCATO = 'MANCATO';
	
    public function __toString() {
        return $this->descrizione;
    }
}