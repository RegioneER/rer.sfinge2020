<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

/**
 * Description of RicercaProgetti.
 *
 * @author lfontana
 */
class RicercaEsportazione extends AttributiRicerca
{
    /**
     * @var int
     */
    protected $numeroElementiPerPagina;

    /**
     * @var int
     */
    private $num_invio;

    private $stato;

    public function getNumInvio()
    {
        return $this->num_invio;
    }

    public function setNumInvio($num_invio)
    {
        $this->num_invio = $num_invio;
    }

    public function getStato()
    {
        return $this->stato;
    }

    public function setStato($stato)
    {
        $this->stato = $stato;
    }

    public function getType()
    {
        return "MonitoraggioBundle\Form\Ricerca\RicercaEsportazioneType";
    }

    public function getNomeRepository()
    {
        return 'MonitoraggioBundle:MonitoraggioEsportazione';
    }

    public function getNomeMetodoRepository()
    {
        return 'findAllEsportazioni';
    }

    public function getNumeroElementiPerPagina()
    {
        return $this->numeroElementiPerPagina;
    }

    public function setNumeroElementiPerPagina($numeroElementiPerPagina)
    {
        $this->numeroElementiPerPagina = $numeroElementiPerPagina;
    }

    public function getNomeParametroPagina()
    {
        return 'page';
    }
}
