<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Entity\TabelleContesto;

use MonitoraggioBundle\Form\Entity\TabelleContesto\Base;

class Elenco extends Base{
    
    const TYPE = 'MonitoraggioBundle\Form\Ricerca\ContestoType';

    protected $descrizione;

    protected $codice;
   

    public function getNomeParametroPagina() {
        
    }

    public function getType() {
        return self::TYPE;
    }

    public function getDescrizione() {
        return $this->descrizione;
    }

    public function setDescrizione($descrizione) {
        $this->descrizione = $descrizione;
    }


    

    public function getCodice() {
        return $this->codice;
    }

    public function setCodice($codice) {
        $this->codice = $codice;
    }


}
