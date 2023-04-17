<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Entity;

/**
 * Description of LivelloGerarchico
 *
 * @author lfontana
 */
class LivelloGerarchico {

    public static $LIVELLI = array(
        'FN00' => 'FN00',
        'FN01' => 'FN01',
        'FN02' => 'FN02',
        'FN03' => 'FN03',
        'FN04' => 'FN04',
        'FN05' => 'FN05',
        'FN06' => 'FN06',
        'FN07' => 'FN07',
        'FN08' => 'FN08',
        'FN09' => 'FN09',
        'FN10' => 'FN10',
    );
    /**
     *
     * @var array
     */
    protected $tabelleStruttura;
    
    /**
     *
     * @var \MonitoraggioBundle\Entity\TC36LivelloGerarchico 
     */
    protected $tc36LivelloGerarchico;

    public function getTabelleStruttura() {
        return $this->tabelleStruttura;
    }

    public function getTc36LivelloGerarchico() {
        return $this->tc36LivelloGerarchico;
    }

    public function setTabelleStruttura($tabelleStruttura) {
        $this->tabelleStruttura = $tabelleStruttura;
    }

    public function setTc36LivelloGerarchico($tc36LivelloGerarchico) {
        $this->tc36LivelloGerarchico = $tc36LivelloGerarchico;
    }

    public function __construct(\MonitoraggioBundle\Entity\TC36LivelloGerarchico $tc36LivelloGerarchico) {
        $this->tc36LivelloGerarchico = $tc36LivelloGerarchico;
        $this->tabelleStruttura = explode($tc36LivelloGerarchico->getCodStrutturaProt(), ';');
    }

}
