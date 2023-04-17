<?php

namespace CipeBundle\Entity\Classificazioni;

use CipeBundle\Entity\Classificazioni\CupClassificazione;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use CipeBundle\Entity\Classificazioni\CupNatura;
use CipeBundle\Entity\Classificazioni\CupSottosettore;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CupNatura
 *
 * @author gaetanoborgosano
 * @ORM\Table(name="cup_settori")
 * @ORM\Entity(repositoryClass="CipeBundle\Entity\Classificazioni\CupClassificazioneRepository")
 */
class CupSettore extends CupClassificazione {
	
	/**
	 *
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="CipeBundle\Entity\Classificazioni\CupSottosettore", mappedBy="CupSettore", cascade={"persist"} )
	 */
	protected $CupSottosettori;
	function getCupSottosettori() { return $this->CupSottosettori; }
	function setCupSottosettori(ArrayCollection $CupSottosettori) { $this->CupSottosettori = $CupSottosettori; }
	function addCupSottosettore(CupSottosettore $CupSottosettore) { $this->getCupSottosettori()->add($CupSottosettore); }
	
	/**
	 *
	 * @var CupNatura
	 * @ORM\ManyToMany(targetEntity="CipeBundle\Entity\Classificazioni\CupNatura", mappedBy="CupSettori")
	 */
	protected $CupNature;
	function getCupNature() { return $this->CupNature; }
	function setCupNature(CupNatura $CupNature) { $this->CupNature = $CupNature; }

		
	function checkCupCodiceSottosettore($codice_sottosettore, $return=false) {
		if(\is_null($codice_sottosettore)) return false;
		$CupSottosettori = $this->getCupSottosettori();
		/* @var $CupSottosettore CupSottosettore */
		foreach ($CupSottosettori as $CupSottosettore) {
			if($codice_sottosettore == $CupSottosettore->getCodice()) {
				if($return) return $CupSottosettore;
				return true;
			}
		}
		return false;
	}
	
	public function __construct() {
		$this->CupSottosettori = new ArrayCollection();
		$this->CupNature = new ArrayCollection();

	}


}
