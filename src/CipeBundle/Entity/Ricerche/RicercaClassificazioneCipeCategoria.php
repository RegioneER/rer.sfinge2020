<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CipeBundle\Entity\Ricerche;

use CipeBundle\Entity\Ricerche\RicercaClassificazioneCipe;
/**
 * Contenitore di ricerca per RicercaClassificazioneCipeCategoria
 *
 * @author gaetanoborgosano
 */
class RicercaClassificazioneCipeCategoria extends RicercaClassificazioneCipe {
	
	protected $Sottosettore;
	function getSottosettore() { return $this->Sottosettore; }
	function setSottosettore($Sottosettore) {
		$this->Sottosettore = (!\is_null($Sottosettore) && \is_object($Sottosettore)) ? $Sottosettore->getId() : null;  
		$this->setAddon_criteria("CupSottosettore", $this->getSottosettore());
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
		if(\is_null($this->getFormTypeClassificazione())) return "CipeBundle\Form\RicercaClassificazioneCipeCategoriaType";
		return $this->getFormTypeClassificazione();
	}

}
