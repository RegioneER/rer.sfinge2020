<?php

namespace MonitoraggioBundle\Service\GestoriImpegni;

use MonitoraggioBundle\Service\AGestoreImpegni;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use MonitoraggioBundle\Entity\TC38CausaleDisimpegno;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use CipeBundle\Entity\Classificazioni\CupNatura;

class Privato extends AGestoreImpegni {
    const NATURE_AMMESSE = [
        CupNatura::CONCESSIONE_AIUTI_SOGGETTI_DIVERSI_UNITA_PRODUTTIVE,
        CupNatura::REALIZZAZIONE_LAVORI_PUBBLICI,
    ];

    public function aggiornaImpegniASaldo(): void {
        $atc = $this->richiesta->getAttuazioneControllo();
        $rendicontatoAmmesso = $atc->getImportoRendicontatoAmmessoTotale();
        $totaleImpegni = $this->richiesta->getTotaleImportoImpegni();
        $economiaDiContributo = $this->getEconomia();
        if ($economiaDiContributo <= 0) {
            return;
        }

        $causale = $this->getCausaleDisimpegno(TC38CausaleDisimpegno::MINORI_SPESE);
        $disimpegno = new RichiestaImpegni($this->richiesta);
        $disimpegno->setTc38CausaleDisimpegno($causale);
        $disimpegno->setTipologiaImpegno(RichiestaImpegni::DISIMPEGNO); // D = DISIMPEGNO
        $disimpegno->setImportoImpegno($economiaDiContributo);

        $this->richiesta->addMonImpegni($disimpegno);

        $livellogerarchico = $this->getLivelloGerarchico();
        $ammesso = new ImpegniAmmessi($disimpegno, $livellogerarchico);

        $disimpegno->addMonImpegniAmmessi($ammesso);
    }

    protected function getEconomia(): float {
        $atc = $this->richiesta->getAttuazioneControllo();
        $rendicontatoAmmesso = $atc->getImportoRendicontatoAmmessoTotale();
        $costoAmmesso = $atc->getCostoAmmesso();

        return  $costoAmmesso - $rendicontatoAmmesso;
    }

    public function impegnoNuovoProgetto(): void {
        $istruttoria = $this->richiesta->getIstruttoria();
        $costoAmesso = $istruttoria->getCostoAmmesso();
        $impegno = new RichiestaImpegni($this->richiesta, RichiestaImpegni::IMPEGNO);
        $impegno->setImportoImpegno($costoAmesso);
        $impegno->setDataImpegno($istruttoria->getDataImpegno());
        $this->richiesta->addMonImpegni($impegno);

        $ammesso = new ImpegniAmmessi($impegno);
        $impegno->addMonImpegniAmmessi($ammesso);
    }

    public function mostraSezionePagamento(): bool {
        $tipoOperazione = $this->richiesta->getMonTipoOperazione();
        if(\is_null($tipoOperazione)){
            return false;
        }
        
        $natura = $tipoOperazione->getCodiceNaturaCup();

        return \in_array($natura, self::NATURE_AMMESSE);
    }
}
