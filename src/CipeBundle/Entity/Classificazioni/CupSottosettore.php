<?php

namespace CipeBundle\Entity\Classificazioni;

use CipeBundle\Entity\Classificazioni\CupClassificazione;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use CipeBundle\Entity\Classificazioni\CupSettore;
use CipeBundle\Entity\Classificazioni\CupCategoria;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CupNatura
 *
 * @author gaetanoborgosano
 * @ORM\Table(name="cup_sottosettori",
 *  indexes={
 *      @ORM\Index(name="idx_cup_sottosettore_cup_settore_id", columns={"CupSettore_id"})
 *  })
 * @ORM\Entity(repositoryClass="CipeBundle\Entity\Classificazioni\CupClassificazioneRepository")
 */
class CupSottosettore extends CupClassificazione {
	
	/**
	 *
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="CipeBundle\Entity\Classificazioni\CupCategoria", mappedBy="CupSottosettore", cascade={"persist"} )
	 */
	protected $CupCategorie;
	function getCupCategorie() { return $this->CupCategorie; }
	function setCupCategorie(ArrayCollection $CupCategorie) { $this->CupCategorie = $CupCategorie; }
	function addCupCategoria(CupCategoria $CupCategoria) {$this->getCupCategorie()->add($CupCategoria); }
		
	/**
	 *
	 * @var CupSettore
	 * @ORM\ManyToOne(targetEntity="CipeBundle\Entity\Classificazioni\CupSettore", inversedBy="CupSottosettori")
     * @ORM\JoinColumn(name="CupSettore_id", referencedColumnName="id", nullable=false)
	 */
	protected $CupSettore;
	function getCupSettore() { return $this->CupSettore; }
	function setCupSettore(CupSettore $CupSettore) { $this->CupSettore = $CupSettore; }
	
	
	function checkCupCodiceCategoria($codice_categoria, $return=false) {
		if(\is_null($codice_categoria)) return false;
		$CupCategorie = $this->getCupCategorie();
		/* @var $CupSottosettore CupSottosettore */
		foreach ($CupCategorie as $CupCategoria) {
			if($codice_categoria == $CupCategoria->getCodice()) {
				if($return) return $CupCategoria;
				return true;
			}
		}
		return false;
	}
	
	public function __construct() {
		$this->CupCategorie = new ArrayCollection();
	}
	
}
