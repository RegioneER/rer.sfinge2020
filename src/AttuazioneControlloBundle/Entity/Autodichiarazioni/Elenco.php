<?php

namespace AttuazioneControlloBundle\Entity\Autodichiarazioni;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Elenco
 *
 * @author gdisparti
 */

/**
 * @ORM\Entity()
 * @ORM\Table(name="atd_elenchi")
 */
class Elenco {

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
	 * viene visualizzato come titolo sezione (ad esempio Ipegni, Dichiara, etc etc)
	 * @ORM\Column(name="testo", type="text")
	 */
	protected $testo;
	
	/**
	 * non viene visualizzata, serve solo a contestualizzare la natura dell'elenco
	 * @ORM\Column(name="etichetta", type="string", nullable=true)
	 */
	protected $etichetta;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Autodichiarazioni\ElencoAutodichiarazione", mappedBy="elenco")
	 */
	protected $elencoAutodichiarazioni;
	
	public function __construct() {
		$this->elencoAutodichiarazioni = new ArrayCollection();
	}

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
	
	public function getElencoAutodichiarazioni() {
		return $this->elencoAutodichiarazioni;
	}

	public function setElencoAutodichiarazioni($elencoAutodichiarazioni) {
		$this->elencoAutodichiarazioni = $elencoAutodichiarazioni;
	}

	public function getAutodichiarazioni() {
		$autodichiarazioni = array();
		foreach ($this->elencoAutodichiarazioni as $elencoAutodichiarazione){
			$autodichiarazioni[] = $elencoAutodichiarazione->getAutodichiarazione();
		}
		
		return $autodichiarazioni;
	}
	
	public function getEtichetta() {
		return $this->etichetta;
	}

	public function setEtichetta($etichetta) {
		$this->etichetta = $etichetta;
	}

}
