<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="tipi_voce_spesa")
 */
class TipoVoceSpesa extends EntityLoggabileCancellabile {

	const TOTALE = 'TOTALE';
	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $codice;

    /**
     * @ORM\Column(type="string", length=1024, nullable=false)
     */
    private $descrizione;
	
	function getId() {
		return $this->id;
	}

	function getCodice() {
		return $this->codice;
	}

	function getDescrizione() {
		return $this->descrizione;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setCodice($codice) {
		$this->codice = $codice;
	}

	function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;
	}

    public function __toString() {
        return $this->descrizione;
    }
        
}
