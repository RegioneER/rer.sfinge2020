<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * FascicoloBandoAzienda
 *
 * @ORM\Entity
 * @ORM\Table(name="fascicoli_bandi_aziende")
 * 
 */
class FascicoloBandoAzienda extends EntityLoggabileCancellabile {

	/**
	 * @var string $codice
	 *  
	 * @ORM\Id 
	 * @ORM\Column(type="string", length=50 ) 
	 */
	private $codice;

	/**
	 * @var int $bando
	 *  
	 * @ORM\Id 
	 * @ORM\Column(type="integer") 
	 */
	private $bando;

	/**
	 * @var string $fascicolo
	 *  
	 * @ORM\Column(type="string", length=45 ) 
	 */
	private $fascicolo;

	/**
	 * PRES = presentazione e gestione
	 * REND = rendicontazione
	 * CTRL = controlli loco 
	 * @ORM\Id 
	 * @var string $fascicolo
	 * @ORM\Column(type="string", length=4, nullable=true) 
	 */
	private $tipo;

	/**
	 * Set codice
	 *
	 * @param string $codice
	 *
	 * @return FascicoloBandoAzienda
	 */
	public function setCodice($codice) {
		$this->codice = $codice;

		return $this;
	}

	/**
	 * Get codice
	 *
	 * @return string
	 */
	public function getCodice() {
		return $this->codice;
	}

	/**
	 * Set bando
	 *
	 * @param integer $bando
	 *
	 * @return FascicoloBandoAzienda
	 */
	public function setBando($bando) {
		$this->bando = $bando;

		return $this;
	}

	/**
	 * Get bando
	 *
	 * @return integer
	 */
	public function getBando() {
		return $this->bando;
	}

	/**
	 * Set fascicolo
	 *
	 * @param string $fascicolo
	 *
	 * @return FascicoloBandoAzienda
	 */
	public function setFascicolo($fascicolo) {
		$this->fascicolo = $fascicolo;

		return $this;
	}

	/**
	 * Get fascicolo
	 *
	 * @return string
	 */
	public function getFascicolo() {
		return $this->fascicolo;
	}
	
	public function getTipo() {
		return $this->tipo;
	}

	public function setTipo($tipo) {
		$this->tipo = $tipo;
	}


}
