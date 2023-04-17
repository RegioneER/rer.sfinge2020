<?php

namespace RichiesteBundle\Entity;

use AnagraficheBundle\Entity\Persona;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="referenti",
 *  indexes={
 *      @ORM\Index(name="idx_persona_id", columns={"persona_id"}),
 *		@ORM\Index(name="idx_proponente_id", columns={"proponente_id"}),
 *		@ORM\Index(name="idx_tipo_referente_id", columns={"tipo_referente_id"})
 *  })
 */
class Referente extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AnagraficheBundle\Entity\Persona")
	 * @ORM\JoinColumn(name="persona_id", referencedColumnName="id", nullable=false)
	 */
	private $persona;
	
	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente", inversedBy="referenti")
	 * @ORM\JoinColumn(name="proponente_id", referencedColumnName="id", nullable=true)
	 */
	private $proponente;
	
	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Intervento", inversedBy="referenti")
	 * @ORM\JoinColumn(name="intervento_id", referencedColumnName="id", nullable=true)
	 */
	private $intervento;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\TipoReferenza")
	 * @ORM\JoinColumn(name="tipo_referente_id", referencedColumnName="id", nullable=false)
	 * @Assert\NotNull()
	 */
	private $tipo_referenza;
	
	/**
	 * @ORM\Column(name="qualifica", type="string", length=100, nullable=true)
	 * @Assert\NotNull(groups={"bando_5","bando_6"})
	 * @Assert\Length(max = "100", groups={"bando_5","bando_6"})
	 */
	private $qualifica;
	
	/**
	 * @ORM\Column(name="ruolo", type="string", length=100, nullable=true)
	 * @Assert\NotNull(groups={"bando_65"})
	 * @Assert\Length(max = "100", groups={"bando_65"})
	 */
	private $ruolo;
	
	/**
	 * 
	 * @ORM\Column(name="email_pec", type="string", length=128, nullable=true)
	 * 
	 * @Assert\NotBlank(message="Specificare l'indirizzo email PEC", groups={"bando_5","bando_61","bando_98","bando_99","bando_118","bando_123", "bando_125", "bando_129", "bando130", "bando150", "bando151", "bando152", "bando153", "bando154", "bando163", "bando164"})
	 * @Assert\Length(max = "128")
	 * @Assert\Email()
	 * 
	 */
	protected $email_pec;
	
	function getId() {
		return $this->id;
	}

	/**
	 * @return Persona
	 */
	function getPersona() {
		return $this->persona;
	}

	/**
	 * @return Proponente
	 */
	function getProponente() {
		return $this->proponente;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setPersona($persona) {
		$this->persona = $persona;
	}

	function setProponente($proponente) {
		$this->proponente = $proponente;
	}

	/**
	 * @return TipoReferenza
	 */
	public function getTipoReferenza()
	{
		return $this->tipo_referenza;
	}

	/**
	 * @param TipoReferenza $tipo_referenza
	 */
	public function setTipoReferenza(TipoReferenza $tipo_referenza)
	{
		$this->tipo_referenza = $tipo_referenza;
	}

	public function getSoggetto() {
		return $this->getProponente()->getSoggetto();
	}
	
	function getSoggettoMandatario() {
		return $this->getProponente()->getSoggettoMandatario();
	}	

	function getSoggettoMandatarioDaIntervento() {
		return $this->getIntervento()->getProponente()->getSoggettoMandatario();
	}
	
	public function getQualifica() {
		return $this->qualifica;
	}

	public function setQualifica($qualifica) {
		$this->qualifica = $qualifica;
	}
	
	public function getEmailPec() {
		return $this->email_pec;
	}

	public function setEmailPec($email_pec) {
		$this->email_pec = $email_pec;
	}
	
	public function getIntervento() {
		return $this->intervento;
	}

	public function setIntervento($intervento) {
		$this->intervento = $intervento;
	}

	public function getRuolo() {
		return $this->ruolo;
	}

	public function setRuolo($ruolo) {
		$this->ruolo = $ruolo;
	}

}
