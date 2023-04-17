<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="procedure_dati_cup")
 */
class ProceduraDatiCup extends EntityLoggabileCancellabile {

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(name="id", type="bigint")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="CipeBundle\Entity\Classificazioni\CupNatura")
	 * @ORM\JoinColumn(name="natura_id", referencedColumnName="id", nullable=true)
	 *
	 */
	protected $natura;

	/**
	 * @ORM\ManyToOne(targetEntity="CipeBundle\Entity\Classificazioni\CupSettore")
	 * @ORM\JoinColumn(name="settore_id", referencedColumnName="id", nullable=true)
	 *
	 */
	protected $settore;

	/**
	 * @ORM\ManyToOne(targetEntity="CipeBundle\Entity\Classificazioni\CupTipologia")
	 * @ORM\JoinColumn(name="tipologia_id", referencedColumnName="id", nullable=true)
	 *
	 */
	protected $tipologia;

	/**
	 * @ORM\ManyToOne(targetEntity="CipeBundle\Entity\Classificazioni\CupSottosettore")
	 * @ORM\JoinColumn(name="sotto_settore_id", referencedColumnName="id", nullable=true)
	 *
	 */
	protected $sotto_settore;

	/**
	 * @ORM\ManyToOne(targetEntity="CipeBundle\Entity\Classificazioni\CupCategoria")
	 * @ORM\JoinColumn(name="categoria_id", referencedColumnName="id", nullable=true)
	 *
	 */
	protected $categoria;
	
	/**
	 * @ORM\OneToOne(targetEntity="Procedura", mappedBy="procedura_dati_cup")
	 */
	protected $procedura;
	
	/**
 	 * @ORM\Column(type="string", length=255,  name="desc_intervento", nullable=true)
	 */
	protected $desc_intervento;
	
	
	/**
 	 * @ORM\Column(type="string", length=255,  name="codici_tipologia_cop_finanz", nullable=true)
	 */
	protected $codici_tipologia_cop_finanz;
	
	
	public function getId() {
		return $this->id;
	}

	public function getNatura() {
		return $this->natura;
	}

	public function getSettore() {
		return $this->settore;
	}

	public function getTipologia() {
		return $this->tipologia;
	}

	public function getSottoSettore() {
		return $this->sotto_settore;
	}

	public function getCategoria() {
		return $this->categoria;
	}

	public function getProcedura() {
		return $this->procedura;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setNatura($natura) {
		$this->natura = $natura;
	}

	public function setSettore($settore) {
		$this->settore = $settore;
	}

	public function setTipologia($tipologia) {
		$this->tipologia = $tipologia;
	}

	public function setSottoSettore($sotto_settore) {
		$this->sotto_settore = $sotto_settore;
	}

	public function setCategoria($categoria) {
		$this->categoria = $categoria;
	}

	public function setProcedura($procedura) {
		$this->procedura = $procedura;
	}
	
	function getDescIntervento() {
		return $this->desc_intervento;
	}

	function setDescIntervento($desc_intervento) {
		$this->desc_intervento = $desc_intervento;
	}

	
	function getCodiciIipologiaCopFinanz() { 
		return $this->codici_tipologia_cop_finanz;
	}

	function setCodiciTipologiaCopFinanz($codici_tipologia_cop_finanz) {
		$this->codici_tipologia_cop_finanz = $codici_tipologia_cop_finanz;
	}

	function getArrayCodiciTipologiaCopFinanz() {
		$codici_tipologia_cop_finanz = $this->getCodiciIipologiaCopFinanz();
		$array = explode(",", $codici_tipologia_cop_finanz);
		foreach ($array as $key => $value) {
			$array[$key] = trim($value);
		}
		return $array;
	}
	function setArrayCodiciTipologiaCopFinanz($array_codici_tipologia_cop_finanz) {
		$codici_tipologia_cop_finanz = implode(",", $array_codici_tipologia_cop_finanz);
		$this->setCodiciTipologiaCopFinanz($codici_tipologia_cop_finanz);
	}
	






}
