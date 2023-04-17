<?php
namespace RichiesteBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tipi_settore_attivita")
 */
class TipoSettoreAttivita
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $codice;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
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

	public function __toString(){
		return $this->descrizione ?? '-';
	}
}