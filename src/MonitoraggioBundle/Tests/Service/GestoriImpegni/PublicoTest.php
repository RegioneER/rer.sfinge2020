<?php

namespace MonitoraggioBundle\Tests\Service\GestoriImpegni;

use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use MonitoraggioBundle\Service\GestoriImpegni\Pubblico;

class PublicoTest extends Base {
    public function setUp() {
        parent::setUp();
        $this->setTipologiaSoggetto(true);

        $this->gestore = new Pubblico($this->container, $this->richiesta);
    }

    public function testAggiornaSenzaEconomiaDiContributo() {
        $impegno = $this->addImpegno(RichiestaImpegni::IMPEGNO, 1000);
        $this->setCostoAmmesso(1000);
        $this->addPagamento(1000);

        $this->gestore->aggiornaImpegniASaldo();
        $impegni = $this->richiesta->getMonImpegni();

        $this->assertCount(1, $impegni);
        $this->assertSame($impegno, $impegni->first());
    }

    public function testAggiornaConEconomiaDiContributoNoPagamenti() {
        $impegno = $this->addImpegno(RichiestaImpegni::IMPEGNO, 2000);
        $this->setCostoAmmesso(2000);

        $this->gestore->aggiornaImpegniASaldo();
        $impegni = $this->richiesta->getMonImpegni();

        $this->assertCount(2, $impegni);
        $this->assertContains($impegno, $impegni);
        $disimpegni = $impegni->filter(function (RichiestaImpegni $i) {
            return RichiestaImpegni::DISIMPEGNO == $i->getTipologiaImpegno();
        });
        $this->assertNotEmpty($disimpegni);
        /** @var RichiestaImpegni $disimpegno */
        $disimpegno = $disimpegni->first();
        $this->assertEquals(2000, $disimpegno->getImportoImpegno());
    }

    public function testAggiornaConEconomiaDiContributo() {
        $impegno = $this->addImpegno(RichiestaImpegni::IMPEGNO, 2000);
        $this->setCostoAmmesso(2000);
        $this->addPagamento(1000);

        $this->gestore->aggiornaImpegniASaldo();
        $impegni = $this->richiesta->getMonImpegni();

        $this->assertCount(2, $impegni);
        $this->assertContains($impegno, $impegni);
        $disimpegni = $impegni->filter(function (RichiestaImpegni $i) {
            return RichiestaImpegni::DISIMPEGNO == $i->getTipologiaImpegno();
        });
        $this->assertNotEmpty($disimpegni);
        /** @var RichiestaImpegni $disimpegno */
        $disimpegno = $disimpegni->first();
        $this->assertEquals(1000, $disimpegno->getImportoImpegno());
    }

    public function testVerificaLivellogerarchico() {
        $this->addImpegno(RichiestaImpegni::IMPEGNO, 2000);
        $this->setCostoAmmesso(2000);
        $this->addPagamento(1000);

        $this->gestore->aggiornaImpegniASaldo();
        $impegni = $this->richiesta->getMonImpegni();

        $disimpegni = $impegni->filter(function (RichiestaImpegni $i) {
            return RichiestaImpegni::DISIMPEGNO == $i->getTipologiaImpegno();
        });
        /** @var RichiestaImpegni $disimpegno */
        $disimpegno = $disimpegni->first();
        /** @var ImpegniAmmessi $ammesso */
        $ammesso = $disimpegno->getMonImpegniAmmessi()->first();
        $this->assertEquals(1000, $ammesso->getImportoImpAmm());
        $this->assertNotNull($ammesso->getRichiestaLivelloGerarchico());
    }

    public function testImpegniNuovoProgetto(): void {
        $this->setCostoAmmesso(2000);
        $this->gestore->impegnoNuovoProgetto();

        $this->assertEmpty($this->richiesta->getMonImpegni());
    }
}
