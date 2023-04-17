<?php

namespace MonitoraggioBundle\Form\Entity\Strutture;
use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;

/**
 * Description of PR00
 *
 * @author gorlando
 */
class PR00 extends BaseRicercaStruttura {
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Fase procedurale", property="tc46_fase_procedurale" )
     * @RicercaFormType( ordine = 1, property="tc46_fase_procedurale" , type = "entity", label = "Fase procedurale", options={"class": "MonitoraggioBundle\Entity\PR00IterProgetto"})
     */
	protected $fase_provedurale_id;
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Codice locale progetto" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Codice locale progetto")
     */
	protected $cod_locale_progetto;
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Data inizio prevista" )
     * @RicercaFormType( ordine = 5, type = "birthday", label = "Data inizio prevista", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
	protected $data_inizio_prevista;
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Data inizio effettiva" )
     * @RicercaFormType( ordine = 5, type = "birthday", label = "Data inizio effettiva", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
	protected $data_inizio_effettiva;
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Data fine prevista" )
     * @RicercaFormType( ordine = 5, type = "birthday", label = "Data fine prevista", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
	protected $data_fine_prevista;
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Data fine effettiva" )
     * @RicercaFormType( ordine = 5, type = "birthday", label = "Data fine effettiva", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
	protected $data_fine_effettiva;
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 9, titolo="Flag cancellazione" )
     * @RicercaFormType( ordine = 9, type = "text", label = "Flag cancellazione")
     */
	protected $flg_cancellazione;
	
	function getFaseProveduraleId() {
		return $this->fase_provedurale_id;
	}

	function getCodLocaleProgetto() {
		return $this->cod_locale_progetto;
	}

	function getDataInizioPrevista() {
		return $this->data_inizio_prevista;
	}

	function getDataInizioEffettiva() {
		return $this->data_inizio_effettiva;
	}

	function getDataFinePrevista() {
		return $this->data_fine_prevista;
	}

	function getDataFineEffettiva() {
		return $this->data_fine_effettiva;
	}

	function getFlgCancellazione() {
		return $this->flg_cancellazione;
	}

	function setFaseProveduraleId($fase_provedurale_id) {
		$this->fase_provedurale_id = $fase_provedurale_id;
	}

	function setCodLocaleProgetto($cod_locale_progetto) {
		$this->cod_locale_progetto = $cod_locale_progetto;
	}

	function setDataInizioPrevista($data_inizio_prevista) {
		$this->data_inizio_prevista = $data_inizio_prevista;
	}

	function setDataInizioEffettiva($data_inizio_effettiva) {
		$this->data_inizio_effettiva = $data_inizio_effettiva;
	}

	function setDataFinePrevista($data_fine_prevista) {
		$this->data_fine_prevista = $data_fine_prevista;
	}

	function setDataFineEffettiva($data_fine_effettiva) {
		$this->data_fine_effettiva = $data_fine_effettiva;
	}

	function setFlgCancellazione($flg_cancellazione) {
		$this->flg_cancellazione = $flg_cancellazione;
	}

}
