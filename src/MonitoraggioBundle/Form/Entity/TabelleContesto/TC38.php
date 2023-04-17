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
 * @author lfontana
 */
class TC38 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice causale disimpegno")
      * @ViewElenco( ordine = 1, titolo="Codice causale disimpegno" )
     */
    protected $causale_disimpegno;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione causale disimpegno")
      * @ViewElenco( ordine = 2, titolo="Descrizione causale disimpegno" )
     */
    protected $descrizione_causale_disimpegno;

    /**
     * @return mixed
     */
    public function getCausaleDisimpegno()
    {
        return $this->causale_disimpegno;
    }

    /**
     * @param mixed $causale_disimpegno
     */
    public function setCausaleDisimpegno($causale_disimpegno)
    {
        $this->causale_disimpegno = $causale_disimpegno;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneCausaleDisimpegno()
    {
        return $this->descrizione_causale_disimpegno;
    }

    /**
     * @param mixed $descrizione_causale_disimpegno
     */
    public function setDescrizioneCausaleDisimpegno($descrizione_causale_disimpegno)
    {
        $this->descrizione_causale_disimpegno = $descrizione_causale_disimpegno;
    }


}
