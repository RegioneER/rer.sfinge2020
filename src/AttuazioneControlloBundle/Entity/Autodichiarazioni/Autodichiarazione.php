<?php

namespace AttuazioneControlloBundle\Entity\Autodichiarazioni;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Description of Autodichiarazione
 *
 * @author gdisparti
 */

/**
 * @ORM\Entity()
 * @ORM\Table(name="atd_autodichiarazioni")
 */
class Autodichiarazione {

	/**
	 *
	 * @ORM\Column(name="id", type="bigint")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 *
	 * @ORM\Column(name="codice", type="string", length=50, nullable=true)
	 */
	protected $codice;

	/**
	 *
	 * @ORM\Column(name="testo", type="text")
	 */
	protected $testo;

	public function getId() {
		return $this->id;
	}

	public function getCodice() {
		return $this->codice;
	}

	public function getTesto() {
		return $this->testo;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setCodice($codice) {
		$this->codice = $codice;
	}

	public function setTesto($testo) {
		$this->testo = $testo;
	}

}
