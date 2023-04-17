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
 * Description of TC11
 *
 * @author lfontana
 */
class TC11 extends Base{
    /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice tipo classificazione" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice tipo classificazione")
     */
    protected $tipo_class;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Descrizione tipo classificazione" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione tipo classificazione")
     */
    protected $descrizione_tipo_classificazione;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Origine classificazione" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Origine classificazione")
     */
    protected $origine_classificazione;
    
    public function getTipoClass() {
        return $this->tipo_class;
    }

    public function getDescrizioneTipoClassificazione() {
        return $this->descrizione_tipo_classificazione;
    }

    public function getOrigineClassificazione() {
        return $this->origine_classificazione;
    }

    public function setTipoClass($tipo_class) {
        $this->tipo_class = $tipo_class;
    }

    public function setDescrizioneTipoClassificazione($descrizione_tipo_classificazione) {
        $this->descrizione_tipo_classificazione = $descrizione_tipo_classificazione;
    }

    public function setOrigineClassificazione($origine_classificazione) {
        $this->origine_classificazione = $origine_classificazione;
    }


}
