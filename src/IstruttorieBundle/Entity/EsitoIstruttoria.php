<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EsitoIstruttoria
 *
 * @ORM\Table(name="istruttorie_esiti")
 * @ORM\Entity
 */
class EsitoIstruttoria extends \BaseBundle\Entity\EntityTipo
{
    const AMMESSO = 'AMMESSO';
    const NON_ISTRUIBILE = 'NON_ISTRUIBILE';
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
	
    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $esito_positivo;		
	
	function setId($id) {
		$this->id = $id;
	}

	function getEsitoPositivo() {
		return $this->esito_positivo;
	}

	function setEsitoPositivo($esito_positivo) {
		$this->esito_positivo = $esito_positivo;
	}	
	
	function __toString() {
		return $this->getDescrizione();
	}
}
