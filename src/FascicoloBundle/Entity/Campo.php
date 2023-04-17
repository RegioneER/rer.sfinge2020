<?php

namespace FascicoloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;


/**
 * Description of Campo
 *
 * @author aturdo
 * 
 * @ORM\Table(name="fascicoli_campi")
 * @ORM\Entity(repositoryClass="FascicoloBundle\Entity\CampoRepository")
 */
class Campo {

    /**
	 * @var integer $id
	 * 
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */	
	protected $id;
	
	/**
	 * @var Frammento $frammento
	 * 
	 * @ORM\ManyToOne(targetEntity="Frammento", inversedBy="campi")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	protected $frammento;
	
	/**
	 * @var TipoCampo $tipoCampo
	 * 
	 * @ORM\ManyToOne(targetEntity="TipoCampo")
	 * @ORM\JoinColumn(nullable=false, onDelete="RESTRICT")
	 * @Assert\NotNull
	 */
	protected $tipoCampo;
	
	/**
	 * @var string $label
	 * 
	 * @ORM\Column(name="label", type="text", nullable=false)
	 * @Assert\NotNull
	 */
	protected $label;
	
	/**
	 * @var boolean $required
	 * 
	 * @ORM\Column(name="required", type="boolean", nullable=true)
	 * @Assert\NotNull
	 */
	protected $required;	
	
	/**
	 * @var boolean $evidenziato
	 * 
	 * @ORM\Column(name="evidenziato", type="boolean", nullable=true)
	 */
	protected $evidenziato;
	
	/**
	 * @var array $scelte
	 * 
	 * @ORM\Column(name="scelte", type="array", nullable=true)
	 */
	protected $scelte;
	
	/**
	 * @var boolean $expanded
	 * 
	 * @ORM\Column(name="expanded", type="boolean", nullable=true)
	 */
	protected $expanded;
	
	/**
	 * @var boolean $multiple
	 * 
	 * @ORM\Column(name="multiple", type="boolean", nullable=true)
	 */
	protected $multiple;
	
	/**
	 * @var string $query
	 * 
	 * @ORM\Column(name="query", type="text", nullable=true)
	 */
	protected $query;	
	
	/**
	 * @var integer $ordinamento
	 * 
     * @ORM\Column(name="ordinamento",type="integer",nullable=false)
     */	
	protected $ordinamento;
	
	/**
	 * @var string $alias
	 * 
	 * @ORM\Column(name="alias", type="string", nullable=false, length=255)
	 * 
	 * @Assert\Regex(pattern="/^[a-z0-9_]+$/", message="L'alias può contenere solo lettere minuscole, cifre ed underscore")
	 * @Assert\NotNull
	 * @Assert\Length(max=255, maxMessage="Campo limitato a 255 caratteri")
	 */
	protected $alias;
	
	/**
     * @var Collection $vincoli
     * 
	 * @ORM\OneToMany(targetEntity="Vincolo", mappedBy="campo", cascade={"persist"})
	 */
	protected $vincoli;
	
	/**
	 * @var string $callbackPresenza
	 * 
	 * @Assert\Length(max=255)
	 * @Assert\Regex(pattern="/^[a-zA-Z0-9_]+$/", message="La callback può contenere solo lettere, cifre ed underscore") 
	 * @ORM\Column(name="callbackPresenza", type="string", length=255, nullable=true)
	 */
	protected $callbackPresenza;
	
	/**
	 * @var integer $precisione
	 * 
     * @ORM\Column(name="precisione",type="integer",nullable=true)
     */	
	protected $precisione;
	
	/**
	 * @var string $note
	 * 
	 * @ORM\Column(name="note", type="text", nullable=true)
	 */
	protected $note;
	
	/**
	 * @var integer $righeTextArea
	 * 
     * @ORM\Column(name="righeTextArea",type="integer",nullable=true)
     */	
	protected $righeTextArea;
	
	public function __construct() {
		$this->vincoli= new \Doctrine\Common\Collections\ArrayCollection();
	}
	
	public function getId() {
		return $this->id;
	}

	public function getFrammento() {
		return $this->frammento;
	}

	public function getTipoCampo() {
		return $this->tipoCampo;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setFrammento($frammento) {
		$this->frammento = $frammento;
	}

	public function setTipoCampo($tipoCampo) {
		$this->tipoCampo = $tipoCampo;
	}
	
	public function getLabel() {
		return $this->label;
	}

	public function setLabel($label) {
		$this->label = $label;
	}

	public function getEvidenziato() {
		return $this->evidenziato;
	}

	public function setEvidenziato($evidenziato) {
		$this->evidenziato = $evidenziato;
	}

	public function getScelte() {
		return $this->scelte;
	}

	public function setScelte($scelte) {
		$this->scelte = $scelte;
	}

	public function getExpanded() {
		return $this->expanded;
	}

	public function getMultiple() {
		return $this->multiple;
	}

	public function setExpanded($expanded) {
		$this->expanded = $expanded;
	}

	public function setMultiple($multiple) {
		$this->multiple = $multiple;
	}
	
	public function getRequired() {
		return $this->required;
	}

	public function setRequired($required) {
		$this->required = $required;
	}
	
	public function getQuery() {
		return $this->query;
	}

	public function setQuery($query) {
		$this->query = $query;
	}
	
	function getOrdinamento() {
		return $this->ordinamento;
	}

	function setOrdinamento($ordinamento) {
		$this->ordinamento = $ordinamento;
	}
	
	function getAlias() {
		return $this->alias;
	}

	function setAlias($alias) {
		$this->alias = $alias;
	}
	
	function getVincoli() {
		return $this->vincoli;
	}

	function setVincoli($vincoli) {
		$this->vincoli = $vincoli;
	}
	
	public function getCallbackPresenza() {
		return $this->callbackPresenza;
	}

	public function setCallbackPresenza($callbackPresenza) {
		$this->callbackPresenza = $callbackPresenza;
	}	
	
	function getPrecisione() {
		return $this->precisione;
	}

	function setPrecisione($precisione) {
		$this->precisione = $precisione;
	}
	
	function getNote() {
		return $this->note;
	}

	function setNote($note) {
		$this->note = $note;
	}

	public function getPath() {
		return $this->getFrammento()->getPath().".".$this->getAlias();
	}	

	public function __toString() {
		return $this->getLabel();
	}
	
	function getRigheTextArea() {
		return $this->righeTextArea;
	}

	function setRigheTextArea($righeTextArea) {
		$this->righeTextArea = $righeTextArea;
	}

		
	public function __clone() {
		if($this->id){
			$vincoli = array();
			foreach ($this->vincoli as $vincolo) {
				$vincoloClonato = clone $vincolo;
				$vincoloClonato->setCampo($this);
				$vincoli[] = $vincoloClonato;	
			}
			$this->setVincoli($vincoli);
		}
	}

}
