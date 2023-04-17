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
 * Description of TC10
 *
 * @author lfontana
 */
class TC10 extends Base{

    /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice tipologia localizzazione" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice tipologia localizzazione")
     */
    protected $tipo_localizzazione;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Descrizione tipologia localizzazione" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione tipologia localizzazione")
     */
    protected $descrizione_tipo_localizzazione;
    
    public function getTipoLocalizzazione() {
        return $this->tipo_localizzazione;
    }

    public function getDescrizioneTipoLocalizzazione() {
        return $this->descrizione_tipo_localizzazione;
    }

    public function setTipoLocalizzazione($tipo_localizzazione) {
        $this->tipo_localizzazione = $tipo_localizzazione;
    }

    public function setDescrizioneTipoLocalizzazione($descrizione_tipo_localizzazione) {
        $this->descrizione_tipo_localizzazione = $descrizione_tipo_localizzazione;
    }


}
