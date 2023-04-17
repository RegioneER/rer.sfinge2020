<?php


namespace AttuazioneControlloBundle\Form\Entity\Revoche;

use BaseBundle\Service\AttributiRicerca;

class RicercaAttoRevoca extends AttributiRicerca
{

    private $descrizione;

    private $numero;

    private $dataAttoDa;

    private $dataAttoA;

    function getDescrizione() {
        return $this->descrizione;
    }

    function getNumero() {
        return $this->numero;
    }

    function setDescrizione($descrizione) {
        $this->descrizione = $descrizione;
        return $this;
    }

    function setNumero($numero) {
        $this->numero = $numero;
        return $this;
    }

    public function getDataAttoDa()
    {
        return $this->dataAttoDa;
    }

    public function setDataAttoDa($dataAttoDa)
    {
        $this->dataAttoDa = $dataAttoDa;
    }

    public function getDataAttoA()
    {
        return $this->dataAttoA;
    }

    public function setDataAttoA($dataAttoA)
    {
        $this->dataAttoA = $dataAttoA;
    }

    public function getType()
    {
        return "AttuazioneControlloBundle\Form\RicercaAttoRevocaType";
    }

    public function getNomeRepository()
    {
        return "AttuazioneControlloBundle:Revoche\AttoRevoca";
    }

    public function getNomeMetodoRepository()
    {
        return "cercaAttoRevoca";
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