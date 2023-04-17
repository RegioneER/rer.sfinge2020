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
/**
 * Description of TC16
 *
 * @author lfontana
 */
class TC40 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Tipologia soggetto percettore")
      * @ViewElenco( ordine = 1, titolo="Tipologia soggetto percettore" )
     */
   protected $tipo_percettore;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione tipologia soggetto percettore")
      * @ViewElenco( ordine = 2, titolo="Descrizione tipologia soggetto percettore" )
     */
    protected $descrizione_tipo_percettore;

    

    /**
     * @return mixed
     */
    public function getTipoPercettore()
    {
        return $this->tipo_percettore;
    }

    /**
     * @param mixed $tipo_percettore
     */
    public function setTipoPercettore($tipo_percettore)
    {
        $this->tipo_percettore = $tipo_percettore;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneTipoPercettore()
    {
        return $this->descrizione_tipo_percettore;
    }

    /**
     * @param mixed $descrizione_tipo_percettore
     */
    public function setDescrizioneTipoPercettore($descrizione_tipo_percettore)
    {
        $this->descrizione_tipo_percettore = $descrizione_tipo_percettore;
    }




}
