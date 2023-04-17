<?php

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * BaseBundle\Entity\IndirizzoCatastale
 *
 * @ORM\Table(name="indirizzi_catastali")
 * @ORM\Entity()
 * 
 */
class IndirizzoCatastale extends EntityLoggabileCancellabile {

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
	 * @ORM\Column(name="via", type="string", length=50)
         * @Assert\NotBlank(groups = {"bando_5", "bando_58", "bando_107_a", "bando_189"}) 
ì
	 */
	protected $via;

	/**
	 * @var string $numero_civico
	 *
	 *
	 * @ORM\Column(name="numero_civico", type="string", length=10)
	 * @Assert\NotBlank(groups = {"bando_5", "bando_58", "bando_107_a", "bando_189"}) 
	 */
	protected $numero_civico;

	/**
	 * @var string $cap
	 *
	 *
	 * @ORM\Column(name="cap", type="string", length=5)
	 * @Assert\NotBlank(groups = {"bando_5", "bando_58", "bando_107_a", "bando_189"}) 
	 * @Assert\Length(min = "5", max = "5")
	 */
	protected $cap;

	/**
	 * @var string $comune
	 *
	 * @ORM\ManyToOne(targetEntity="GeoBundle\Entity\GeoComune")
	 * @ORM\JoinColumn(name="comune_id", referencedColumnName="id", nullable=true)
         * @Assert\NotNull(groups = {"bando_5", "bando_58", "bando_107_a", "bando_189"}) 
	 */
	protected $comune;

	
	/**
	 * @var string $foglio
	 *
	 *
	 * @ORM\Column(name="foglio", type="string", length=50, nullable=true)
	 * @Assert\NotBlank(groups = {"bando_5", "bando_58", "bando_107_a", "bando_189"}) 
	 */
	protected $foglio;

	/**
	 * @var string $particella
	 *
	 *
	 * @ORM\Column(name="particella", type="string", length=50, nullable=true)
	 * @Assert\NotBlank(groups = {"bando_5", "bando_58", "bando_107_a", "bando_189"}) 
	 */
	protected $particella;

	/**
	 * @var string $subalterno
	 *
	 *
	 * @ORM\Column(name="subalterno", type="string", length=100, nullable=true)
	 * @Assert\NotBlank(groups = {"bando_5", "bando_58", "bando_107_a", "bando_189"}) 
	 */
	protected $subalterno;
	
	protected $disabilitaCombo;

	public function getId() {
		return $this->id;
	}

	public function getVia() {
		return $this->via;
	}

	public function getNumeroCivico() {
		return $this->numero_civico;
	}

	public function getCap() {
		return $this->cap;
	}

	public function getComune() {
		return $this->comune;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setVia($via) {
		$this->via = $via;
	}

	public function setNumeroCivico($numero_civico) {
		$this->numero_civico = $numero_civico;
	}

	public function setCap($cap) {
		$this->cap = $cap;
	}

	public function setComune($comune) {
		$this->comune = $comune;
	}
	
	public function getProvincia() {
		return $this->getComune() ? $this->getComune()->getProvincia() : null;
	}

	public function setProvincia($provincia) {
		
	}
	
	public function getFoglio() {
		return $this->foglio;
	}

	public function getParticella() {
		return $this->particella;
	}

	public function getSubalterno() {
		return $this->subalterno;
	}

	public function setFoglio($foglio) {
		$this->foglio = $foglio;
	}

	public function setParticella($particella) {
		$this->particella = $particella;
	}

	public function setSubalterno($subalterno) {
		$this->subalterno = $subalterno;
	}

	public function getDisabilitaCombo() {
		return $this->disabilitaCombo;
	}

	public function setDisabilitaCombo($disabilitaCombo) {
		$this->disabilitaCombo = $disabilitaCombo;
	}
	
	public function __toString() {
		$indirizzo = $this->getNumeroCivico() ? $this->getVia() . ", " . $this->getNumeroCivico() : $this->getVia();
		$citta = $this->getComune();
		return $indirizzo . " " . $citta;
	}

}
