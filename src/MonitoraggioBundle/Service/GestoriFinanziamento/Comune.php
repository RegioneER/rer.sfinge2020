<?php

namespace MonitoraggioBundle\Service\GestoriFinanziamento;

use MonitoraggioBundle\Service\AGestoreFinanziamento;
use AttuazioneControlloBundle\Entity\Finanziamento;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;
use MonitoraggioBundle\Entity\TC34DeliberaCIPE;
use MonitoraggioBundle\Entity\TC35Norma;

class Comune extends AGestoreFinanziamento {
    protected function calcolaFinanziamento() {
        $costoAmmesso = $this->getCostoAmmesso(); // Il totale ammesso - COSTO TOTALE DEL PROGETTO
        $contributoConcesso = $this->getContributoConcesso(); // Il massimo contributo erogabile

        //Privato
        $finanziamentoComune = $costoAmmesso - $contributoConcesso;
        $this->setFinanziamento(TC33FonteFinanziaria::COMUNE, '99999', '99999', $finanziamentoComune);

        //UE
        $contributoUE = \round($contributoConcesso * Finanziamento::FINANZIAMENTO_UE, 2);
        $this->setFinanziamento(TC33FonteFinanziaria::FESR, '99999', '99999', $contributoUE);

        // STATO
        $contributoStato = \round($contributoConcesso * Finanziamento::FINANZIAMENTO_STATO, 2);
        $this->setFinanziamento(TC33FonteFinanziaria::STATO, '99999', '202', $contributoStato);

        // REGIONE
        $contributoRegione = $contributoConcesso - $contributoUE - $contributoStato;
        $this->setFinanziamento(TC33FonteFinanziaria::REGIONE, '99999', '99999', $contributoRegione);
    }

    protected function getFinanziamento(TC33FonteFinanziaria $fondo, TC34DeliberaCIPE $delibera, TC35Norma $norma): Finanziamento {
        $finanziamento = parent::getFinanziamento($fondo, $delibera, $norma);
        if (TC33FonteFinanziaria::COMUNE == $fondo->getCodFondo()) {
            $mandatario = $this->richiesta->getSoggetto();
            $geo = $mandatario->getComune();
            $finanziamento->setTc16LocalizzazioneGeografica($geo->getTc16LocalizzazioneGeografica());
        }

        return $finanziamento;
    }
}
