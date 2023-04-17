<?php

namespace MonitoraggioBundle\Form\Entity;
use BaseBundle\Service\AttributiRicerca;

/**
 * Description of Base
 *
 * @author lfontana
 */
class RicercaProcedura extends AttributiRicerca{

    protected $numeroElementiPerPagina;

    /**
    * @var \SfingeBundle\Entity\Asse
    */
    protected $asse;
    protected $tipo;
    protected $numeroProceduraAttivazione;
    protected $titolo;

    protected $stato;

    /**
    * @var boolean
    */
    protected $porFesr;

    /**
    * @var boolean
    */
    protected $datiCompleti;

    public function getAsse() {
        return $this->asse;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getNumeroProceduraAttivazione() {
        return $this->numeroProceduraAttivazione;
    }

    public function getTitolo() {
        return $this->titolo;
    }

    

    public function getStato() {
        return $this->stato;
    }

    public function getPorFesr() {
        return $this->porFesr;
    }

    public function getDatiCompleti() {
        return $this->datiCompleti;
    }

    public function setAsse(\SfingeBundle\Entity\Asse $asse = null) {
        $this->asse = $asse;
        return $this;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
        return $this;
    }

    public function setNumeroProceduraAttivazione($numeroProceduraAttivazione) {
        $this->numeroProceduraAttivazione = $numeroProceduraAttivazione;
        return $this;
    }

    public function setTitolo($titolo) {
        $this->titolo = $titolo;
        return $this;
    }

    

    public function setStato($stato) {
        $this->stato = $stato;
        return $this;
    }

    public function setPorFesr($porFesr) {
        $this->porFesr = $porFesr;
        return $this;
    }

    public function setDatiCompleti($datiCompleti) {
        $this->datiCompleti = $datiCompleti;
        return $this;
    }

     /* METODI DI ATTRIBUTO RICERCA */
    public function getNomeParametroPagina() {
        
    }

    public function getNumeroElementiPerPagina() {
        return $this->numeroElementiPerPagina;
    }

    public function getType() {
        return 'MonitoraggioBundle\Form\Ricerca\ProceduraType';
    }
    
    public function setNumeroElementiPerPagina($numeroElementiPerPagina) {
        $this->numeroElementiPerPagina = $numeroElementiPerPagina;
    }

    public function getNomeMetodoRepository() {
        return 'findAllMonitoraggioSearch';
    }
    
    public function getNomeRepository() {
        return 'SfingeBundle:Procedura';
    }

}