<?php

namespace MonitoraggioBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;
use MonitoraggioBundle\Form\Ricerca\RicercaProgettoType;
use SfingeBundle\Entity\Asse;

class RicercaValidazioneIGRUE extends RicercaProgetto {
    
    public function getNomeRepository(): string {
        return "RichiesteBundle:Richiesta";
    }

    public function getNomeMetodoRepository(): string {
        return "getReportIgrue";
    }

}
