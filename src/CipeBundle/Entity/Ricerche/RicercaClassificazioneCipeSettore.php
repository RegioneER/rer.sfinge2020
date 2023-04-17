<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CipeBundle\Entity\Ricerche;

use CipeBundle\Entity\Ricerche\RicercaClassificazioneCipe;

/**
 * Contenitore di ricerca per RicercaClassificazioneCipeSettore
 *
 * @author gaetanoborgosano
 */
class RicercaClassificazioneCipeSettore extends RicercaClassificazioneCipe {
	
	protected $Natura;
	function getNatura() { return $this->Natura; }
	function setNatura($Natura) { 
		$this->Natura = (!\is_null($Natura) && \is_object($Natura)) ? $Natura->getId() : null;  
		$this->setAddon_criteria("CupNatura", $this->getNatura()); 
	}

			
	// IAttributiRicerca interface
	
	public function getNomeMetodoRepository() {
		return "ricercaClassificazioneCipeSettore";
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
		if(\is_null($this->getFormTypeClassificazione())) return "CipeBundle\Form\RicercaClassificazioneCipeRicNaturaType";
		return $this->getFormTypeClassificazione();
	}

}
