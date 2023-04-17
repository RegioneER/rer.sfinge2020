<?php

namespace MonitoraggioBundle\Service\GestoriFinanziamento;

use MonitoraggioBundle\Service\AGestoreFinanziamento;
use AttuazioneControlloBundle\Entity\Finanziamento;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;

class Regione extends AGestoreFinanziamento {
    protected function calcolaFinanziamento() {
        $costoAmmesso = $this->getCostoAmmesso(); // Il totale ammesso - COSTO TOTALE DEL PROGETTO
        $contributoConcesso = $this->getContributoConcesso(); // Il massimo contributo erogabile

        //UE
        $contributoUE = \round($contributoConcesso * Finanziamento::FINANZIAMENTO_UE, 2);
        $this->setFinanziamento(TC33FonteFinanziaria::FESR, '99999', '99999', $contributoUE);

        // STATO
        $contributoStato = \round($contributoConcesso * Finanziamento::FINANZIAMENTO_STATO, 2);
        $this->setFinanziamento(TC33FonteFinanziaria::STATO, '99999', '202', $contributoStato);

        // REGIONE
        $contributoRegione = $costoAmmesso - $contributoUE - $contributoStato;
        $this->setFinanziamento(TC33FonteFinanziaria::REGIONE, '99999', '99999', $contributoRegione);
    }
}
