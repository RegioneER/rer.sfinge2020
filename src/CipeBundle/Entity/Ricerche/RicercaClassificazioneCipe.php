<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CipeBundle\Entity\Ricerche;

use BaseBundle\Service\AttributiRicerca;

/**
 * Contenitore di ricerca per RicercaClassificazioneCipe
 *
 * @author gaetanoborgosano
 */
class RicercaClassificazioneCipe extends AttributiRicerca {
	
	
	protected $addons_criteria=array();
	function getAddons_criteria() { return $this->addons_criteria; }
	function setAddons_criteria($addons_criteria) { $this->addons_criteria = $addons_criteria; }
	function setAddon_criteria($param, $value, $like=null) {
		$addons_criteria = $this->getAddons_criteria();
		$addons_criteria[$param] = array("value" => $value, "like" => $like);
		$this->setAddons_criteria($addons_criteria);
	}
	
		
	protected $codice;
	function getCodice() { return $this->codice; }
	function setCodice($codice) { $this->codice = $codice; }

	protected $descrizione;
	function getDescrizione() { return $this->descrizione; }
	function setDescrizione($descrizione) { $this->descrizione = $descrizione; }

		
	protected $tipoClassificazione=null;
	function getTipoClassificazione() { return $this->tipoClassificazione; }
	function setTipoClassificazione($tipoClassificazione) { $this->tipoClassificazione = $tipoClassificazione; }

	protected $formTypeClassificazione=null;
	function getFormTypeClassificazione() { return $this->formTypeClassificazione; }
	function setFormTypeClassificazione($formTypeClassificazione) { $this->formTypeClassificazione = $formTypeClassificazione; }

		
	public function __construct() {
		$this->setAddons_criteria(array());
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
		if(\is_null($this->getFormTypeClassificazione())) return "CipeBundle\Form\RicercaClassificazioneCipeType";
		return $this->getFormTypeClassificazione();
	}

}
