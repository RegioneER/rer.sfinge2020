<?php

namespace MonitoraggioBundle\Service\GestoriFinanziamento;

use MonitoraggioBundle\Service\AGestoreFinanziamento;
use AttuazioneControlloBundle\Entity\Finanziamento;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneElementoChecklistPagamento;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;


class Pubblico extends AGestoreFinanziamento
{
    protected function calcolaFinanziamento() {
        $costoAmmesso = $this->getCostoAmmesso(); // Il totale ammesso - COSTO TOTALE DEL PROGETTO
        $contributoConcesso = $this->getContributoConcesso(); // Il massimo contributo erogabile

		//Altro pubblico
        $finaziamentoAltroPubblico = $costoAmmesso - $contributoConcesso;
        if ($finaziamentoAltroPubblico > 0.0) {
            $this->setFinanziamento(TC33FonteFinanziaria::ALTRO_PUBBLICO, '99999', '99999', $finaziamentoAltroPubblico);
        }
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
	
}