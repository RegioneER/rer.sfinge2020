<?php

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VisibilitaStato
 *
 * @ORM\Entity
 * @ORM\Table(name="visibilita_stati")
 */
class VisibilitaStato {

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(name="id", type="bigint")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var \BaseBundle\Entity\Stato $stato

	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\Stato", inversedBy="visibilita")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $stato;

	/**
	 * @var string $ruolo
	 * @ORM\Column(name="ruolo", type="string", length=255, nullable=false)
	 */
	protected $ruolo;

	public function getId() {
		return $this->id;
	}

	public function getStato() {
		return $this->stato;
	}

	public function getRuolo() {
		return $this->ruolo;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setStato($stato) {
		$this->stato = $stato;
	}

	public function setRuolo($ruolo) {
		$this->ruolo = $ruolo;
	}

}
