<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Entity\TabelleContesto;

use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;

/**
 * Description of TC9
 *
 * @author lfontana
 */
class TC30 extends Base{
    /**
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice durata ricerca" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice durata ricerca")
     */
    protected $durata_ricerca;

    /**
     * @var string
     * @ViewElenco( ordine = 2, titolo="Descrizione durata ricerca" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione durata ricerca")
     */
    protected $descrizione_durata_ricerca;

    /**
     * @return mixed
     */
    public function getDurataRicerca()
    {
        return $this->durata_ricerca;
    }

    /**
     * @param mixed $durata_ricerca
     */
    public function setDurataRicerca($durata_ricerca)
    {
        $this->durata_ricerca = $durata_ricerca;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneDurataRicerca()
    {
        return $this->descrizione_durata_ricerca;
    }

    /**
     * @param mixed $descrizione_durata_ricerca
     */
    public function setDescrizioneDurataRicerca($descrizione_durata_ricerca)
    {
        $this->descrizione_durata_ricerca = $descrizione_durata_ricerca;
    }



}
