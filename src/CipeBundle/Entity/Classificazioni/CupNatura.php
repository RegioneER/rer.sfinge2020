<?php

namespace CipeBundle\Entity\Classificazioni;

use CipeBundle\Entity\Classificazioni\CupClassificazione;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use CipeBundle\Entity\Classificazioni\CupSettore;
use Doctrine\Common\Collections\Collection;
use RichiesteBundle\Entity\FaseNatura;

/**
 * @author gaetanoborgosano
 * @ORM\Table(name="cup_nature")
 * @ORM\Entity(repositoryClass="CipeBundle\Entity\Classificazioni\CupClassificazioneRepository")
 */
class CupNatura extends CupClassificazione {

	const REALIZZAZIONE_BENI_SERVIZI = '02';
	const REALIZZAZIONE_LAVORI_PUBBLICI = '03';
	const CONCESSIONE_INCENTIVI_ATTIVITA_PRODUTTIVE = '07';
	const CONCESSIONE_AIUTI_SOGGETTI_DIVERSI_UNITA_PRODUTTIVE = '06';
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $formazione;


	function getFormazione() {
		return $this->formazione;
	}

	function setFormazione($formazione) {
		$this->formazione = $formazione;
	}

	/**
	 * @var Collection
	 * @ORM\ManyToMany(targetEntity="CipeBundle\Entity\Classificazioni\CupSettore", inversedBy="CupNature")
	 * @ORM\JoinTable(name="cup_nature_settori",
	 *      joinColumns={@ORM\JoinColumn(name="natura_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="settore_id", referencedColumnName="id")}
	 *      )
	 */
	protected $CupSettori;

	function getCupSettori() {
		return $this->CupSettori;
	}

	function setCupSettori(ArrayCollection $CupSettori) {
		$this->CupSettori = $CupSettori;
	}

	function addCupSettore(CupSettore $CupSettore) {
		$this->getCupSettori()->add($CupSettore);
	}

	function checkCupCodiceSettore($codice_settore, $return = false) {
		if (\is_null($codice_settore))
			return false;
		$CupSettori = $this->getCupSettori();
		foreach ($CupSettori as $CupSettore) {
			if ($codice_settore == $CupSettore->getCodice()) {
				if ($return)
					return $CupSettore;
				return true;
			}
		}
		return false;
	}

	/**
	 *
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="CipeBundle\Entity\Classificazioni\CupTipologia", mappedBy="CupNatura", cascade={"persist"} )
	 */
	protected $CupTipologie;

	function getCupTipologie() {
		return $this->CupTipologie;
	}

	function setCupTipologie(Collection $CupTipologie) {
		$this->CupTipologie = $CupTipologie;
	}

	function addCupTipologia(CupTipologia $CupTipologia) {
		$this->getCupTipologie()->add($CupTipologia);
	}

	function checkCupCodiceTipologia($codice_tipologia, $return = false) {
		if (\is_null($codice_tipologia))
			return false;
		$CupTipologie = $this->getCupTipologie();
		foreach ($CupTipologie as $CupTipologia) {
			if ($codice_tipologia == $CupTipologia->getCodice()) {
				if ($return)
					return $CupTipologia;
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @var Collection
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\FaseNatura", mappedBy="natura" )
	 */
	protected $fasi_natura;
	
	public function getFasiNatura() {
		return $this->fasi_natura;
	}

	public function setFasiNatura(Collection $fasi_natura) {
		$this->fasi_natura = $fasi_natura;
	}

	public function addFasiNatura(FaseNatura $fase): self{
		$this->fasi_natura[] = $fase;

		return $this;
	}
	
	public function __construct() {
		$this->CupSettori = new ArrayCollection();
		$this->CupTipologie = new ArrayCollection();
		$this->fasi_natura = new ArrayCollection();
	}

}
