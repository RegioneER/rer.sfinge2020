<?php

namespace CipeBundle\Entity\Tracciati;

use Doctrine\ORM\Mapping as ORM;


/**
 * Description of DatiRichiesta
 *
 * @author gaetanoborgosano
 * @ORM\Table(name="tracciati_batch_nature_tipologie")
 * @ORM\Entity()
 */

class TracciatoBatchNatureTipologie {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	function getId() { return $this->id; }
	function setId($id) { $this->id = $id; }

	/**
	 *
	 * @ORM\Column(type="string", length=2, nullable=false)
	 */
	protected $tracciato;
	
	/**
	 *
	 * @ORM\Column(type="string", length=255, name="tipo_descrizione", nullable=false)
	 */
	protected $tipoDescrizione;
	
	/**
	 *
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $descrizione;
	
	/**
	 *
	 * @ORM\Column(type="datetime", name="data_inizio_validita", nullable=false)
	 */
	protected $dataInizioValidita;
	
	/**
	 *
	 * @ORM\Column(type="datetime", name="data_fine_validita", nullable=true)
	 */
	protected $dataFineValidita;
	
	/**
	 *
	 * @ORM\Column(type="string", length=2, name="codice_natura", nullable=false)
	 */
	protected $codiceNatura;
	
	/**
	 *
	 * @ORM\Column(type="string", length=2, name="codice_tipologia", nullable=false)
	 */
	protected $codiceTipologia;
	
	/**
	 *
	 * @ORM\Column(type="string", length=1, name="flag_cumulativo", nullable=false)
	 */
	protected $flagCumulativo;
	
	/**
	 *
	 * @ORM\Column(type="string", length=255, name="descrizione_natura", nullable=false)
	 */
	protected $descrizioneNatura;
	
	/**
	 *
	 * @ORM\Column(type="string", length=255, name="descrizione_tipologia", nullable=false)
	 */
	protected $descrizioneTipologia;
	
	
	function getTracciato() {
		return $this->tracciato;
	}

	function getDescrizione() {
		return $this->descrizione;
	}

	function getDataInizioValidita() {
		return $this->dataInizioValidita;
	}

	function getDataFineValidita() {
		return $this->dataFineValidita;
	}

	function getCodiceNatura() {
		return $this->codiceNatura;
	}

	function getCodiceTipologia() {
		return $this->codiceTipologia;
	}

	function getFlagCumulativo() {
		return $this->flagCumulativo;
	}

	function getDescrizioneNatura() {
		return $this->descrizioneNatura;
	}

	function getDescrizioneTipologia() {
		return $this->descrizioneTipologia;
	}

	function setTracciato($tracciato) {
		$this->tracciato = $tracciato;
	}

	function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;
	}

	function setDataInizioValidita($dataInizioValidita) {
		$this->dataInizioValidita = $dataInizioValidita;
	}

	function setDataFineValidita($dataFineValidita) {
		$this->dataFineValidita = $dataFineValidita;
	}

	function setCodiceNatura($codiceNatura) {
		$this->codiceNatura = $codiceNatura;
	}

	function setCodiceTipologia($codiceTipologia) {
		$this->codiceTipologia = $codiceTipologia;
	}

	function setFlagCumulativo($flagCumulativo) {
		$this->flagCumulativo = $flagCumulativo;
	}

	function setDescrizioneNatura($descrizioneNatura) {
		$this->descrizioneNatura = $descrizioneNatura;
	}

	function setDescrizioneTipologia($descrizioneTipologia) {
		$this->descrizioneTipologia = $descrizioneTipologia;
	}
	
	function getTipoDescrizione() {
		return $this->tipoDescrizione;
	}

	function setTipoDescrizione($tipoDescrizione) {
		$this->tipoDescrizione = $tipoDescrizione;
	}






	
}
