<?php

namespace AuditBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="verificatori")
 */
class Verificatore extends EntityLoggabileCancellabile{

	/**
	 * @var integer $id
	 *
	 *
	 * @ORM\Column(name="id", type="bigint")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string $nome
	 *
	 * @ORM\Column(name="nome", type="string", length=50)
	 */
	protected $nome;

	/**
	 * @var string $cognome
	 *
	 * @ORM\Column(name="cognome", type="string", length=50)
	 */
	protected $cognome;

	public function getId() {
        return $this->id;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getCognome() {
        return $this->cognome;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setCognome($cognome) {
        $this->cognome = $cognome;
    }

    public function __toString() {
        return $this->nome." ".$this->cognome;
    }
}
