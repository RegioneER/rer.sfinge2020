<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CipeBundle\Entity\Classificazioni;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of CipeClassification
 *
 * @author gaetanoborgosano
 */
class CupClassificazione {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string", length=16, nullable=false)
	 */
	protected $codice;
	
	/**
	 * @ORM\Column(type="string", length=1255, nullable=false)
	 */
	protected $descrizione;
	
	function getId() { return $this->id; }
	function setId($id) { $this->id = $id; }
	function getCodice() { return $this->codice; }
	function setCodice($codice) { $this->codice = $codice; }
	function getDescrizione() { return $this->descrizione; }
	function setDescrizione($descrizione) { $this->descrizione = $descrizione; }

	function __toString() {
		return $this->descrizione;
	}
	/** Trasforma la classificazione in un oggetto standard contenente id e descrizione
	 * @return \stdClass
	 */
	public function toStdObject(){
		// $res = StdClass();
		// $res->descrizione = $this->descrizione;
		// $res->id = $this->id;
		$res = array(
			'id' => $this->id,
			'descrizione' => $this->descrizione,
		);
		return $res;
	}
}
