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
class TC9 extends Base{
    /**
     * @var string
     * @ViewElenco( ordine = 1, titolo="Livello istruzione" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Livello istruzione")
     */
    protected $liv_istituzione_str_fin;
    
    /**
     * @var string
     * @ViewElenco( ordine = 2, titolo="Descrizione livello istruzione" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione livello istruzione")
     */
    protected $descrizione_livello_istituzione;
    
    public function getLivIstituzioneStrFin() {
        return $this->liv_istituzione_str_fin;
    }

    public function getDescrizioneLivelloIstituzione() {
        return $this->descrizione_livello_istituzione;
    }

    public function setLivIstituzioneStrFin($liv_istituzione_str_fin) {
        $this->liv_istituzione_str_fin = $liv_istituzione_str_fin;
    }

    public function setDescrizioneLivelloIstituzione($descrizione_livello_istituzione) {
        $this->descrizione_livello_istituzione = $descrizione_livello_istituzione;
    }


}
