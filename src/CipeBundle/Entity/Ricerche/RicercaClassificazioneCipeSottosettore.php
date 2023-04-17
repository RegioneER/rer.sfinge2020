<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CipeBundle\Entity\Ricerche;

use CipeBundle\Entity\Ricerche\RicercaClassificazioneCipe;
/**
 * Contenitore di ricerca per RicercaClassificazioneCipeSottosettore
 *
 * @author gaetanoborgosano
 */
class RicercaClassificazioneCipeSottosettore extends RicercaClassificazioneCipe {
	
	protected $Settore;
	function getSettore() { return $this->Settore; }
	function setSettore($Settore) {
		$this->Settore = (!\is_null($Settore) && \is_object($Settore)) ? $Settore->getId() : null;  
		$this->setAddon_criteria("CupSettore", $this->getSettore()); 
	}

			
	// IAttributiRicerca interface
	
	public function getNomeMetodoRepository() {
		return "ricercaClassificazioneCipe";
	}

	public function getNomeParametroPagina() {
		return null;
	}

	public function getNomeRepository() {
		return "CipeBundle\Entity\Classificazioni\\".$this->getTipoClassificazione();
	}

	public function getNumeroElementiPerPagina() {
		return null;
	}

	public function getType() {
		if(\is_null($this->getFormTypeClassificazione())) return "CipeBundle\Form\RicercaClassificazioneCipeSottosettoreType";
		return $this->getFormTypeClassificazione();
	}

}
