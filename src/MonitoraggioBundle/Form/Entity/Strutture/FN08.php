<?php

namespace MonitoraggioBundle\Form\Entity\Strutture;
use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;

/**
 * Description of FN08
 *
 * @author gorlando
 */
class FN08 extends BaseRicercaStruttura {
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Percettore", property="tc40_tipo_percettore" )
     * @RicercaFormType( ordine = 1, property="tc40_tipo_percettore" , type = "entity", label = "Percettore", options={"class": "MonitoraggioBundle\Entity\FN08Percettori"})
     */
	protected $tipo_percettore_id;
	
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
     * @ViewElenco( ordine = 3, titolo="Codice pagamento" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Codice pagamento")
     */
	protected $cod_pagamento;
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Tipologia pagamento" )
     * @RicercaFormType( ordine = 4, type = "choice", label = "Tipologia pagamento", options={"placeholder":"-", "choices":{"P":"Pagamento", "R":"Rettifica", "P-TR":"Pagamento per trasferimento","R-TR":"Rettifica per trasferimento"}})
     */
	protected $tipologia_pag;
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 5, titolo="Data pagamento" )
     * @RicercaFormType( ordine = 5, type = "birthday", label = "Data pagamento", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
	protected $data_pagamento;
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 6, titolo="Codice fiscale" )
     * @RicercaFormType( ordine = 6, type = "text", label = "Codice fiscale")
     */
	protected $codice_fiscale;
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 7, titolo="Soggetto pubblico" )
     * @RicercaFormType( ordine = 7, type = "choice", label = "Soggetto pubblico", options={"choices":{"S":"sì","N":"No"}})
     */
	protected $flag_soggetto_pubblico;
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 8, titolo="Importo" )
     * @RicercaFormType( ordine = 8, type = "moneta", label = "Importo")
     */
	protected $importo;
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 9, titolo="Flag cancellazione" )
     * @RicercaFormType( ordine = 9, type = "text", label = "Flag cancellazione")
     */
	protected $flg_cancellazione;
	
	function getTipoPercettoreId() {
		return $this->tipo_percettore_id;
	}

	function getCodLocaleProgetto() {
		return $this->cod_locale_progetto;
	}

	function getCodPagamento() {
		return $this->cod_pagamento;
	}

	function getTipologiaPag() {
		return $this->tipologia_pag;
	}

	function getDataPagamento() {
		return $this->data_pagamento;
	}

	function getCodiceFiscale() {
		return $this->codice_fiscale;
	}

	function getFlagSoggettoPubblico() {
		return $this->flag_soggetto_pubblico;
	}

	function getImporto() {
		return $this->importo;
	}

	function getFlgCancellazione() {
		return $this->flg_cancellazione;
	}

	function setTipoPercettoreId($tipo_percettore_id) {
		$this->tipo_percettore_id = $tipo_percettore_id;
	}

	function setCodLocaleProgetto($cod_locale_progetto) {
		$this->cod_locale_progetto = $cod_locale_progetto;
	}

	function setCodPagamento($cod_pagamento) {
		$this->cod_pagamento = $cod_pagamento;
	}

	function setTipologiaPag($tipologia_pag) {
		$this->tipologia_pag = $tipologia_pag;
	}

	function setDataPagamento($data_pagamento) {
		$this->data_pagamento = $data_pagamento;
	}

	function setCodiceFiscale($codice_fiscale) {
		$this->codice_fiscale = $codice_fiscale;
	}

	function setFlagSoggettoPubblico($flag_soggetto_pubblico) {
		$this->flag_soggetto_pubblico = $flag_soggetto_pubblico;
	}

	function setImporto($importo) {
		$this->importo = $importo;
	}

	function setFlgCancellazione($flg_cancellazione) {
		$this->flg_cancellazione = $flg_cancellazione;
	}


}
