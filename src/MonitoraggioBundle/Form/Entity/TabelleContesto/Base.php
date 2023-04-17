<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Entity\TabelleContesto;

use BaseBundle\Service\AttributiRicerca;
use MonitoraggioBundle\Entity\ElencoTabelleContesto;
use MonitoraggioBundle\Service\GestoreTabelleContestoBase;
/**
 * Description of Base
 *
 * @author lfontana
 */
abstract class Base extends AttributiRicerca{
    const BASE_FORM_TYPE = 'Base';
    const BASE_METODO_RICERCA = 'findAllFiltered';
    const NAMESPACE_RICERCA = 'MonitoraggioBundle\Form\Ricerca\\';
    
    protected $numeroElementiPerPagina;    
    protected $tabella;
    
    public function __construct(ElencoTabelleContesto $tabella = null){
        $this->tabella = $tabella;
    }
    
    public function getNomeParametroPagina() {
        
    }

    public function getNumeroElementiPerPagina() {
        return $this->numeroElementiPerPagina;
    }

    public function getType() {
        if(!is_null($this->tabella)){
            $classe = self::NAMESPACE_RICERCA . GestoreTabelleContestoBase::getSuffisso($this->tabella) .'Type';
            if( class_exists($classe)){
                return $classe;
            }
        }
        return self::NAMESPACE_RICERCA.  self::BASE_FORM_TYPE .'Type';               
    }
    
    public function setNumeroElementiPerPagina($numeroElementiPerPagina) {
        $this->numeroElementiPerPagina = $numeroElementiPerPagina;
    }

    public function getNomeMetodoRepository() {
        return self::BASE_METODO_RICERCA;
    }
    
    public function getNomeRepository() {
        return 'MonitoraggioBundle:' . (is_null($this->tabella) ? 'ElencoTabelleContesto' :  $this->tabella->getClasseEntity());
    }

}
