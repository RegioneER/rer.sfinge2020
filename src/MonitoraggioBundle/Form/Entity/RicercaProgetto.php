<?php

namespace MonitoraggioBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;
use MonitoraggioBundle\Form\Ricerca\RicercaProgettoType;
use SfingeBundle\Entity\Asse;

class RicercaProgetto extends AttributiRicerca {
    protected $numeroElementiPerPagina;

    private $procedura;
    private $codice_locale_progetto;
    private $beneficiario;
    private $codice_cup;
    private $codice_fiscale_beneficiario;
    private $asse;

    public function getProcedura() {
        return $this->procedura;
    }

    public function setProcedura($procedura) {
        $this->procedura = $procedura;
    }

    public function getCodiceLocaleProgetto() {
        return $this->codice_locale_progetto;
    }

    public function getBeneficiario() {
        return $this->beneficiario;
    }

    public function setCodiceLocaleProgetto($codice_locale_progetto) {
        $this->codice_locale_progetto = $codice_locale_progetto;
    }

    public function setBeneficiario($beneficiario) {
        $this->beneficiario = $beneficiario;
    }

    public function getCodiceCup() {
        return $this->codice_cup;
    }

    public function getCodiceFiscaleBeneficiario() {
        return $this->codice_fiscale_beneficiario;
    }

    public function setCodiceCup($codice_cup) {
        $this->codice_cup = $codice_cup;
    }

    public function setCodiceFiscaleBeneficiario($codice_fiscale_beneficiario) {
        $this->codice_fiscale_beneficiario = $codice_fiscale_beneficiario;
    }

    public function getType(): string {
        return RicercaProgettoType::class;
    }

    public function getNomeRepository(): string {
        return "RichiesteBundle:Richiesta";
    }

    public function getNomeMetodoRepository(): string {
        return "getProgetti";
    }

    public function getNumeroElementiPerPagina() {
        return $this->numeroElementiPerPagina;
    }

    public function setNumeroElementiPerPagina($numeroElementiPerPagina) {
        $this->numeroElementiPerPagina = $numeroElementiPerPagina;
    }

    public function getNomeParametroPagina(): string {
        return "page";
    }

    public function setAsse(?Asse $asse) {
        $this->asse = $asse;
    }

    public function getAsse(): ?Asse {
        return $this->asse;
    }
}
