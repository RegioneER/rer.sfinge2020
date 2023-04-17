<?php

namespace CipeBundle\Entity\Classificazioni;

use CipeBundle\Entity\Classificazioni\CupClassificazione;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use CipeBundle\Entity\Classificazioni\CupSettore;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CupNatura
 *
 * @author gaetanoborgosano
 * @ORM\Table(name="cup_finalita")
 * @ORM\Entity(repositoryClass="CipeBundle\Entity\Classificazioni\CupClassificazioneRepository")
 */
class CupFinalita extends CupClassificazione {

	/**
	 * @ORM\Column(type="string", length=1, nullable=false)
	 */
	protected $tipo;
	
	/**
	 * @ORM\Column(type="string", length=1, name="tipo_attestato", nullable=true)
	 */
	protected $tipoAttestato;
	
	/**
	 * @ORM\Column(type="string", length=1, nullable=false)
	 */
	protected $stato;
	
	function getTipo() {
		return $this->tipo;
	}

	function getTipoAttestato() {
		return $this->tipoAttestato;
	}

	function getStato() {
		return $this->stato;
	}

	function setTipo($tipo) {
		$this->tipo = $tipo;
	}

	function setTipoAttestato($tipoAttestato) {
		$this->tipoAttestato = $tipoAttestato;
	}

	function setStato($stato) {
		$this->stato = $stato;
	}


}
