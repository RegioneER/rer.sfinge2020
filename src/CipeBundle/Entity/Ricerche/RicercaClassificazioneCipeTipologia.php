<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CipeBundle\Entity\Ricerche;

use CipeBundle\Entity\Ricerche\RicercaClassificazioneCipe;
/**
 * Contenitore di ricerca per WsGeneraCup
 *
 * @author gaetanoborgosano
 */
class RicercaClassificazioneCipeTipologia extends RicercaClassificazioneCipe {
	
	protected $Natura;
	function getNatura() { return $this->Natura; }
	function setNatura($Natura) { 
		$this->Natura = (!\is_null($Natura) && \is_object($Natura)) ? $Natura->getId() : null;  
		$this->setAddon_criteria("CupNatura", $this->getNatura()); }

	protected $formazione;
	function getFormazione() { return $this->formazione; }
	function setFormazione($formazione) { $this->formazione = $formazione; $this->setAddon_criteria("formazione", $formazione); }

			
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
		if(\is_null($this->getFormTypeClassificazione())) return "CipeBundle\Form\RicercaClassificazioneCipeTipologiaType";
		return $this->getFormTypeClassificazione();
	}

}
