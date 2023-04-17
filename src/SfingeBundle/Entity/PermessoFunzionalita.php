<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PermessoFunzionalita
 *
 * @ORM\Entity()
 * @ORM\Table(name="permessi_funzionalita")
 */
class PermessoFunzionalita extends \BaseBundle\Entity\EntityTipo  
{
	/**
	 *
	 * @ORM\ManyToMany(targetEntity="Utente", mappedBy="permessi_funzionalita")
	 */
	protected $utenti;

	public function getUtenti() {
		return $this->utenti;
	}

	public function setUtenti($utenti) {
		$this->utenti = $utenti;
	}
	
    public function __toString() {

        return $this->getDescrizione();
    }	
}
