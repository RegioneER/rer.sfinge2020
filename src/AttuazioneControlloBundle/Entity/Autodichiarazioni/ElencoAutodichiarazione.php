<?php

namespace AttuazioneControlloBundle\Entity\Autodichiarazioni;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Description of ElencoAutodichiarazione
 *
 * @author gdisparti
 */

/**
 * @ORM\Entity()
 * @ORM\Table(name="atd_elenchi_autodichiarazioni")
 */
class ElencoAutodichiarazione {

	/**
	 *
	 * @ORM\Column(name="id", type="bigint")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Autodichiarazioni\Elenco", inversedBy="elencoAutodichiarazioni")
	 * @ORM\JoinColumn(name="elenco_id", nullable=false)
	 */
	protected $elenco;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Autodichiarazioni\Autodichiarazione")
	 * @ORM\JoinColumn(name="autodichiarazione_id", nullable=false)
	 */
	protected $autodichiarazione;

	public function getId() {
		return $this->id;
	}

	public function getElenco() {
		return $this->elenco;
	}

	public function getAutodichiarazione() {
		return $this->autodichiarazione;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setElenco($elenco) {
		$this->elenco = $elenco;
	}

	public function setAutodichiarazione($autodichiarazione) {
		$this->autodichiarazione = $autodichiarazione;
	}

}
