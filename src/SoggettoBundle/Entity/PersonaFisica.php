<?php
namespace SoggettoBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="SoggettoBundle\Entity\PersonaFisicaRepository")
 */
class PersonaFisica extends Soggetto
{
    /**
     * @return string
     */
	public function getTipo(): string
    {
        return "PERSONA_FISICA";
    }
}