<?php

namespace AttuazioneControlloBundle\Form\Entity\Controlli;

use BaseBundle\Service\AttributiRicerca;

class RicercaControlliProcedura extends AttributiRicerca {

    private $procedura;
    private $azione;
    private $asse;
    private $atto;

    public function getProcedura() {
        return $this->procedura;
    }

    public function getAzione() {
        return $this->azione;
    }

    public function getAsse() {
        return $this->asse;
    }

    public function setProcedura($procedura) {
        $this->procedura = $procedura;
    }

    public function setAzione($azione) {
        $this->azione = $azione;
    }

    public function setAsse($asse) {
        $this->asse = $asse;
    }

    public function getAtto() {
        return $this->atto;
    }

    public function setAtto($atto) {
        $this->atto = $atto;
    }

    public function getType() {
        return "AttuazioneControlloBundle\Form\Controlli\RicercaControlliProceduraType";
    }

    public function getNomeRepository() {
        return "AttuazioneControlloBundle:Controlli\ControlloProcedura";
    }

    public function getNomeMetodoRepository() {
        return "cercaControlliProcedure";
    }

    public function getNumeroElementiPerPagina() {
        return null;
    }

    public function getNomeParametroPagina() {
        return "page";
    }

}
