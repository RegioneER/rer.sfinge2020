<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CipeBundle\Entity\Ricerche;

use BaseBundle\Service\AttributiRicerca;

/**
 * Contenitore di ricerca per WsGeneraCup
 *
 * @author gaetanoborgosano
 */
class RicercaWsGeneraCup extends AttributiRicerca {
	
	
	protected $idRichiesta;
	function getIdRichiesta() { return $this->idRichiesta; }
	function setIdRichiesta($idRichiesta) { $this->idRichiesta = $idRichiesta; }

	protected $idProgetto;
	function getIdProgetto() { return $this->idProgetto; }
	function setIdProgetto($idProgetto) { $this->idProgetto = $idProgetto; }
	
	protected $richiestaInoltrata;
	function getRichiestaInoltrata() { return $this->richiestaInoltrata; }
	function setRichiestaInoltrata($richiestaInoltrata) { $this->richiestaInoltrata = $richiestaInoltrata; }
	
	protected $esito;
	function getEsito() { return $this->esito; }
	function setEsito($esito) { $this->esito = $esito; }
	
	protected $richiestaValida;
	function getRichiestaValida() { return $this->richiestaValida; }
	function setRichiestaValida($richiestaValida) { $this->richiestaValida = $richiestaValida; }
	
	protected $curlError;
	function getCurlError() { return $this->curlError; }
	function setCurlError($curlError) { $this->curlError = $curlError; }
	


	// IAttributiRicerca interface
	
	public function getNomeMetodoRepository() {
		return "ricercaWsGeneraCup";
	}

	public function getNomeParametroPagina() {
		return null;
	}

	public function getNomeRepository() {
		return "CipeBundle:WsGeneraCup";
	}

	public function getNumeroElementiPerPagina() {
		return null;
	}

	public function getType() {
		return "CipeBundle\Form\RicercaWsGeneraCupType";
		
	}

}
