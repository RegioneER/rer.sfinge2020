<?php

namespace MonitoraggioBundle\Service\GestoriFinanziamento;

use MonitoraggioBundle\Service\AGestoreFinanziamento;
use AttuazioneControlloBundle\Entity\Finanziamento;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;
use MonitoraggioBundle\Entity\TC34DeliberaCIPE;
use MonitoraggioBundle\Entity\TC35Norma;

class Privato extends AGestoreFinanziamento {
    protected function calcolaFinanziamento() {
        $costoAmmesso = $this->getCostoAmmesso(); // Il totale ammesso - COSTO TOTALE DEL PROGETTO
        $contributoConcesso = $this->getContributoConcesso(); // Il massimo contributo erogabile

        //Privato
        $finaziamentoPrivato = $costoAmmesso - $contributoConcesso;
        if($finaziamentoPrivato > 0.0){
            $this->setFinanziamento(TC33FonteFinanziaria::PRIVATO, '99999', '99999', $finaziamentoPrivato);
        }

        //UE
        $contributoUE = \round($contributoConcesso * Finanziamento::FINANZIAMENTO_UE, 2);
        $this->setFinanziamento('ERDF', '99999', '99999', $contributoUE);

        // STATO
        $contributoStato = \round($contributoConcesso * Finanziamento::FINANZIAMENTO_STATO, 2);
        $this->setFinanziamento('FDR', '99999', '202', $contributoStato);

        // REGIONE
        $contributoRegione = $contributoConcesso - $contributoUE - $contributoStato;
        $this->setFinanziamento('FPREG', '99999', '99999', $contributoRegione);
    }

    protected function isNecessarioRicalcoloFinanziamento(): bool {
        $istruttoria = $this->richiesta->getIstruttoria();
        $privato = $istruttoria->isSoggettoPrivato();
        $ricalcoloNecessario = $privato && parent::isNecessarioRicalcoloFinanziamento();

        return $ricalcoloNecessario;
    }

    protected function getFinanziamento(TC33FonteFinanziaria $fondo, TC34DeliberaCIPE $delibera, TC35Norma $norma): Finanziamento {
        $finanziamento = parent::getFinanziamento($fondo, $delibera, $norma);
        if (TC33FonteFinanziaria::PRIVATO == $fondo->getCodFondo()) {
            $mandatario = $this->richiesta->getSoggetto();
            $finanziamento->setCofinanziatore($mandatario);
        }

        return $finanziamento;
    }
}
