<?php

namespace SoggettoBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaOperatoriRichiesta extends AttributiRicerca
{

    protected $soggetto_id;

    protected $nome;

    protected $cognome;

    protected $codice_fiscale;

    protected $email;

    protected $denominazione;

	public function getSoggettoId() {
        return $this->soggetto_id;
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

    public function getEmail() {
        return $this->email;
    }

    public function getDenominazione() {
        return $this->denominazione;
    }

    public function setSoggettoId($soggetto_id) {
        $this->soggetto_id = $soggetto_id;
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

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setDenominazione($denominazione) {
        $this->denominazione = $denominazione;
    }

        public function getType()
    {
        return "SoggettoBundle\Form\RicercaOperatoriRichiestaType";
    }

    public function getNomeRepository()
    {
        return "AnagraficheBundle:Persona";
    }

    public function getNomeMetodoRepository()
    {
        return "cercaOperatoriRichiesta";
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