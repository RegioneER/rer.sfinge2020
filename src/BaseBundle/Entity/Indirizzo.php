<?php

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use SoggettoBundle\Entity\Soggetto;

/**
 * BaseBundle\Entity\Indirizzo
 *
 * @ORM\Table(name="indirizzi")
 * @ORM\Entity(repositoryClass="BaseBundle\Entity\IndirizzoRepository")
 * 
 * @Assert\Callback(callback="checkSelezioneStato")
 */
class Indirizzo {

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(name="id", type="bigint")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string $via
	 *
	 *
	 * @ORM\Column(name="via", type="string", length=50, nullable=true)
	 * @Assert\NotBlank(groups={"persona","sede"}) 
	 */
	protected $via;

	/**
	 * @var string $numero_civico
	 *
	 *
	 * @ORM\Column(name="numero_civico", type="string", length=30, nullable=true)
	 * @Assert\NotBlank(groups={"persona","sede"})
	 */
	protected $numero_civico;

	/**
	 * @var string $cap
	 *
	 *
	 * @ORM\Column(name="cap", type="string", length=10, nullable=true)
	 * @Assert\NotBlank(groups={"persona","sede"})
	 */
	protected $cap;

	/**
	 * @var GeoBundle\Entity\GeoStato $stato_residenza
	 *
	 *
	 * @ORM\ManyToOne(targetEntity="GeoBundle\Entity\GeoStato", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 * 
	 * @Assert\NotNull(message="Devi selezionare lo stato",groups={"persona","sede"}) 
	 */
	protected $stato;

	/**
	 * @var string $comune
	 *
	 * @ORM\ManyToOne(targetEntity="GeoBundle\Entity\GeoComune")
	 * @ORM\JoinColumn(name="comune_id", referencedColumnName="id", nullable=true)
	 */
	protected $comune;

	/**
	 * @var string $localita
	 * @ORM\Column(name="localita", type="string", length=100, nullable=true)
	 */
	protected $localita;

	/**
	 * @var string $note
	 * @ORM\Column(name="note", type="text", nullable=true)
	 */
	protected $note;

	/**
	 * @var string $provinciaEstera
	 *
	 *
	 * @ORM\Column(name="provinciaEstera", type="string", length=100, nullable=true)
	 */
	protected $provinciaEstera;

	/**
	 * @var string $comuneEstero
	 *
	 *
	 * @ORM\Column(name="comuneEstero", type="string", length=100, nullable=true)
	 */
	protected $comuneEstero;
	
	protected $disabilitaCombo;
			
	function __construct() {
		
	}

	
	/**
	 * Get id
	 *
	 * @return integer 
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set via
	 *
	 * @param string $via
	 */
	public function setVia($via) {
		$this->via = $via;
	}

	/**
	 * Get via
	 *
	 * @return string 
	 */
	public function getVia() {
		return $this->via;
	}

	/**
	 * Set numero_civico
	 *
	 * @param string $numeroCivico
	 */
	public function setNumeroCivico($numeroCivico) {
		$this->numero_civico = $numeroCivico;
	}

	/**
	 * Get numero_civico
	 *
	 * @return string 
	 */
	public function getNumeroCivico() {
		return $this->numero_civico;
	}

	/**
	 * Set comune
	 *
	 * @param string $comune
	 */
	public function setComune($comune) {
		$this->comune = $comune;
	}

	/**
	 * Get comune
	 *
	 * @return \GeoBundle\Entity\GeoComune
	 */
	public function getComune() {
		return $this->comune;
	}

	/**
	 * Set localita
	 *
	 * @param string $localita
	 */
	public function setLocalita($localita) {
		$this->localita = $localita;
	}

	/**
	 * Get localita
	 *
	 * @return string 
	 */
	public function getLocalita() {
		return $this->localita;
	}

	/**
	 * Set note
	 *
	 * @param string $note
	 */
	public function setNote(string $note) {
		$this->note = $note;
	}

	/**
	 * Get note
	 *
	 * @return string 
	 */
	public function getNote(): ?string {
		return $this->note;
	}

	public function getProvincia() {
		return $this->getComune() ? $this->getComune()->getProvincia() : null;
	}

	public function setProvincia($provincia) {
		
	}

	public function getStato() {
		return $this->stato;
	}

	public function setStato($stato) {
		$this->stato = $stato;
	}

	public function getCap() {
		return $this->cap;
	}

	public function setCap($cap) {
		$this->cap = $cap;
	}

	public function getProvinciaEstera() {
		return $this->provinciaEstera;
	}

	public function setProvinciaEstera(?string $provinciaEstera = '') {
		$this->provinciaEstera = $provinciaEstera;
	}

	public function getComuneEstero() {
		return $this->comuneEstero;
	}

	public function setComuneEstero(?string $comuneEstero = '') {
		$this->comuneEstero = $comuneEstero;
	}
	
	function getDisabilitaCombo() {
		return $this->disabilitaCombo;
	}

	function setDisabilitaCombo($disabilitaCombo) {
		$this->disabilitaCombo = $disabilitaCombo;
	}

	
	public function __toString() {
		$indirizzo = $this->getNumeroCivico() ? $this->getVia() . ", " . $this->getNumeroCivico() : $this->getVia();
		$citta = $this->getComune() ? $this->getComune() : $this->getComuneEstero() . "-" . $this->getStato();
		return $indirizzo . " " . $citta;
	}
	
	public function getViaNumeroCap() {
		$indirizzo = $this->getNumeroCivico() ? $this->getVia() . ", " . $this->getNumeroCivico() : $this->getVia();
		return $indirizzo;
	}

	/**
	 * validazione in base allo stato
	 *
	 */
	public function checkSelezioneStato(ExecutionContextInterface $context) {
		if ($this->getStato()) {
			if ($this->getStato()->getDenominazione() == "Italia") {
				if (is_null($this->getProvincia())) {
					$context->buildViolation('Devi selezionare provincia e comune se lo stato è Italia')
						->atPath('provincia')
						->addViolation();
				}
				if (is_null($this->getComune())) {
					$context->buildViolation('Devi selezionare provincia e comune se lo stato è Italia')
						->atPath('comune')
						->addViolation();
				}if (!\preg_match("/^\d{5}$/", $this->getCap())) {
					$context->buildViolation('Il cap deve essere costituito da cinque cifre se lo stato è Italia')
						->atPath('cap')
						->addViolation();
				}

			} else {
				if (is_null($this->getComuneEstero())) {
					$context->buildViolation('Devi indicare almeno la città se lo stato è diverso da Italia')
						->atPath('comuneEstero')
						->addViolation();
				}
			}
		}

	}

	/**
	 * @param Soggetto $soggetto
	 * @return self
	 */
	public static function IndirizzoFromSoggetto(Soggetto $soggetto){
		$indirizzo = new Indirizzo();
		$indirizzo->setVia($soggetto->getVia());
		$indirizzo->setCap( $soggetto->getCap());
		$indirizzo->setNumeroCivico($soggetto->getCivico());
		$indirizzo->setStato($soggetto->getStato());
		$indirizzo->setComune($soggetto->getComune());
		$indirizzo->setLocalita($soggetto->getLocalita());
		$indirizzo->setProvinciaEstera($soggetto->getProvinciaEstera());
		$indirizzo->setComuneEstero($soggetto->getComuneEstero());
		return $indirizzo;
	}

}
