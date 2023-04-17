<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 07/01/16
 * Time: 13:03
 */

namespace BaseBundle\Service;


abstract class AttributiRicerca implements IAttributiRicerca
{
    protected $numero_elementi;

	protected $filtro_attivo;
	
	protected $consenti_ricerca_vuota;
	
	protected $bypass_max_elementi_per_pagina = false;


	/**
     * @return mixed
     */
    public function getNumeroElementi()
    {
        return $this->numero_elementi;
    }

    /**
     * @param mixed $numero_elementi
     */
    public function setNumeroElementi($numero_elementi)
    {
        $this->numero_elementi = $numero_elementi;
    }
	
	function getFiltroAttivo() {
		return $this->filtro_attivo;
	}

	function setFiltroAttivo($filtro_attivo) {
		$this->filtro_attivo = $filtro_attivo;
	}

    public function mostraNumeroElementi()
    {
        return true;
    }
	
	public function isRicercaVuota() {
		foreach ($this as $key => $value) {
			if (in_array($key, array("numero_elementi", "filtro_attivo", "consenti_ricerca_vuota", "bypass_max_elementi_per_pagina", "opzioni"))) {
				continue;
			}
			
			if (!is_null($value)) {
				return false;
			}
		}
		
		return true;
	}

	function getConsentiRicercaVuota() {
		return $this->consenti_ricerca_vuota;
	}

	function setConsentiRicercaVuota($consenti_ricerca_vuota) {
		$this->consenti_ricerca_vuota = $consenti_ricerca_vuota;
	}

	function getBypassMaxElementiPerPagina() {
		return $this->bypass_max_elementi_per_pagina;
	}

	function setBypassMaxElementiPerPagina($bypass_max_elementi_per_pagina) {
		$this->bypass_max_elementi_per_pagina = $bypass_max_elementi_per_pagina;
	}

	function mergeFreshData($freshData) {

	}

}