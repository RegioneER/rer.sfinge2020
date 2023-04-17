<?php


namespace AttuazioneControlloBundle\Form\Entity;


class RicercaPersonaPagamento extends \BaseBundle\Service\AttributiRicerca {
	
	protected $utente;
	protected $nome;
	protected $cognome;
	protected $codice_fiscale;
	
	public function getUtente() {
		return $this->utente;
	}

	public function getNome() {
		return $this->nome;
	}

	public function getCognome() {
		return $this->cognome;
	}

	public function getCodiceFiscale() {
		return $this->codice_fiscale;
	}

	public function setUtente($utente) {
		$this->utente = $utente;
	}

	public function setNome($nome) {
		$this->nome = $nome;
	}

	public function setCognome($cognome) {
		$this->cognome = $cognome;
	}

	public function setCodiceFiscale($codice_fiscale) {
		$this->codice_fiscale = $codice_fiscale;
	}

		
	public function getType()
    {
        return "AttuazioneControlloBundle\Form\RicercaPersonaPagamentoType";
    }


	public function getNomeRepository()
	{
		return "AnagraficheBundle:Persona";
	}

	public function getNomeMetodoRepository()
	{
		return "cercaPersonePagamento";
	}

	public function getNumeroElementiPerPagina()
	{
		return null;
	}

	public function getNomeParametroPagina()
	{
		return "page";
	}

}
