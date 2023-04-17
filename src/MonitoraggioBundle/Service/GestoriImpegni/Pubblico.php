<?php

namespace MonitoraggioBundle\Service\GestoriImpegni;

use MonitoraggioBundle\Service\AGestoreImpegni;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use MonitoraggioBundle\Entity\TC38CausaleDisimpegno;

class Pubblico extends AGestoreImpegni {
    /**
     * in caso di rendicontato ammesso totale a saldo inferiore al costo totale del progetto,
     * andrà registrato un disimpegno pari alla differenza tra totale degli impegni e
     * rendicontato ammesso totale a saldo, alla data di validazione della check-list
     * del saldo e con causale 02.
     */
    public function aggiornaImpegniASaldo(): void {
        $atc = $this->richiesta->getAttuazioneControllo();
        $costoAmmesso = $atc->getCostoAmmesso();
        $rendicontatoAmmesso = $atc->getImportoRendicontatoAmmessoTotale();
        if ($rendicontatoAmmesso >= $costoAmmesso) {
            return;
        }
        $causale = $this->getCausaleDisimpegno(TC38CausaleDisimpegno::MINORI_SPESE);
        $importoImpegni = $this->richiesta->getTotaleImportoImpegni();
        $importoDisimpegno = $importoImpegni - $rendicontatoAmmesso;
        $disimpegno = new RichiestaImpegni($this->richiesta);
        $disimpegno->setTc38CausaleDisimpegno($causale);
        $disimpegno->setTipologiaImpegno(RichiestaImpegni::DISIMPEGNO); // D = DISIMPEGNO
        $disimpegno->setImportoImpegno($importoDisimpegno);

        $this->richiesta->addMonImpegni($disimpegno);

        $livellogerarchico = $this->getLivelloGerarchico();
        $ammesso = new ImpegniAmmessi($disimpegno, $livellogerarchico);

        $disimpegno->addMonImpegniAmmessi($ammesso);
    }

    public function impegnoNuovoProgetto(): void {
    }

    public function mostraSezionePagamento(): bool {
        if($this->richiesta->getProcedura()->getId() == 8) {
            return false;
        }
        else {
            return true;
        }
    }
}
