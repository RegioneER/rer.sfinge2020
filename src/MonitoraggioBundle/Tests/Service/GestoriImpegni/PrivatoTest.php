<?php

namespace MonitoraggioBundle\Tests\Service\GestoriImpegni;

use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use MonitoraggioBundle\Service\GestoriImpegni\Privato;
use MonitoraggioBundle\Entity\TC38CausaleDisimpegno;
use MonitoraggioBundle\Repository\TC38CausaleDisimpegnoRepository;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use AttuazioneControlloBundle\Entity\Revoche\Revoca;
use AttuazioneControlloBundle\Entity\Revoche\AttoRevoca;

class PrivatoTest extends Base {
    public function setUp() {
        parent::setUp();

        $this->setTipologiaSoggetto(false);
        $this->gestore = new Privato($this->container, $this->richiesta);

        $tc38Repository = $this->createMock(TC38CausaleDisimpegnoRepository::class);
        $tc38Repository->method('findOneBy')->willReturn(new TC38CausaleDisimpegno());

        $this->em->method('getRepository')->will($this->returnValueMap([
            [TC38CausaleDisimpegno::class, $tc38Repository],
        ]));
    }

    public function testAggiornaSenzaEconomiaDiContributo(): void {
        $impegno = $this->addImpegno(RichiestaImpegni::IMPEGNO, 1000);
        $this->setCostoAmmesso(1000);
        $this->addPagamento(1000);

        $this->gestore->aggiornaImpegniASaldo();
        $impegni = $this->richiesta->getMonImpegni();

        $this->assertCount(1, $impegni);
        $this->assertSame($impegno, $impegni->first());
    }

    public function testAggiornaConEconomiaDiContributoNoPagamenti(): void {
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

    public function testImpegniNuovoProgettoPresenzaImpegno(): void {
        $this->richiesta->getIstruttoria()->setCostoAmmesso(2000.00);

        $this->gestore->impegnoNuovoProgetto();
        $impegni = $this->richiesta->getMonImpegni();

        $this->assertCount(1, $impegni);
    }

    public function testImpegniNuovoProgettoImportoImpegno(): void {
        $this->richiesta->getIstruttoria()->setCostoAmmesso(2000.00);

        $this->gestore->impegnoNuovoProgetto();
        /** @var RichiestaImpegni $impegno */
        $impegno = $this->richiesta->getMonImpegni()->first();

        $this->assertNotFalse($impegno);
        $this->assertEquals(RichiestaImpegni::IMPEGNO, $impegno->getTipologiaImpegno());
        $this->assertEquals(2000, $impegno->getImportoImpegno(), '', 0.001);
    }

    public function testImpegniNuovoProgettoTipologiaImpegno(): void {
        $this->richiesta->getIstruttoria()->setCostoAmmesso(2000.00);

        $this->gestore->impegnoNuovoProgetto();
        /** @var RichiestaImpegni $impegno */
        $impegno = $this->richiesta->getMonImpegni()->first();

        $this->assertNotFalse($impegno);
        $this->assertEquals(RichiestaImpegni::IMPEGNO, $impegno->getTipologiaImpegno());
    }

    public function testImpegniNuovoProgettoCodiceImpegno(): void {
        $this->richiesta->getIstruttoria()->setCostoAmmesso(2000.00);

        $this->gestore->impegnoNuovoProgetto();
        /** @var RichiestaImpegni $impegno */
        $impegno = $this->richiesta->getMonImpegni()->first();

        $this->assertNotFalse($impegno);
        $this->assertEquals('PG/2018/123456_I_1', $impegno->getCodice());
    }

    public function testDataImpegno(): void {
        $istruttoria = $this->richiesta->getIstruttoria();
        $istruttoria->setCostoAmmesso(2000.00);
        $data = new \DateTime();
        $istruttoria->setDataImpegno($data);

        $this->gestore->impegnoNuovoProgetto();
        /** @var RichiestaImpegni $impegno */
        $impegno = $this->richiesta->getMonImpegni()->first();

        $this->assertNotFalse($impegno);
        $this->assertSame($data, $impegno->getDataImpegno());
    }

    public function testPresenzaImpegnoAmmesso(): void {
        $this->richiesta->getIstruttoria()->setCostoAmmesso(2000.00);

        $this->gestore->impegnoNuovoProgetto();
        /** @var RichiestaImpegni $impegno */
        $impegno = $this->richiesta->getMonImpegni()->first();
        $this->assertNotFalse($impegno);
        $ammessi = $impegno->getMonImpegniAmmessi();

        $this->assertCount(1, $ammessi);
    }

    public function testImportoImpegnoAmmesso(): void {
        $this->richiesta->getIstruttoria()->setCostoAmmesso(2000.00);

        $this->gestore->impegnoNuovoProgetto();
        /** @var RichiestaImpegni $impegno */
        $impegno = $this->richiesta->getMonImpegni()->first();
        $this->assertNotFalse($impegno);
        /** @var ImpegniAmmessi $ammesso */
        $ammesso = $impegno->getMonImpegniAmmessi()->first();

        $this->assertEquals(2000.00, $ammesso->getImportoImpAmm());
    }

    public function testLivelloGerarchicoImpegnoAmmesso(): void {
        $this->richiesta->getIstruttoria()->setCostoAmmesso(2000.00);

        $this->gestore->impegnoNuovoProgetto();
        /** @var RichiestaImpegni $impegno */
        $impegno = $this->richiesta->getMonImpegni()->first();
        $this->assertNotFalse($impegno);
        /** @var ImpegniAmmessi $ammesso */
        $ammesso = $impegno->getMonImpegniAmmessi()->first();

        $this->assertNotNull($ammesso->getRichiestaLivelloGerarchico());
    }

    public function testDataImpegnoAmmesso(): void {
        $istruttoria = $this->richiesta->getIstruttoria();
        $istruttoria->setCostoAmmesso(2000.00);
        $data = new \DateTime();
        $istruttoria->setDataImpegno($data);

        $this->gestore->impegnoNuovoProgetto();
        /** @var RichiestaImpegni $impegno */
        $impegno = $this->richiesta->getMonImpegni()->first();
        $this->assertNotFalse($impegno);
        /** @var ImpegniAmmessi $ammesso */
        $ammesso = $impegno->getMonImpegniAmmessi()->first();

        $this->assertSame($data, $ammesso->getDataImpAmm());
        $this->assertNotNull($ammesso->getRichiestaLivelloGerarchico());
        $richiestaLivello = $ammesso->getRichiestaLivelloGerarchico();
        $this->assertSame($this->livelloObiettivo, $richiestaLivello->getTc36LivelloGerarchico());
    }

    public function testAggiungiRevocaSenzaDataAtto(): void {
        $attoRevoca = new AttoRevoca();

        $revoca = new Revoca();
        $revoca->setAttoRevoca($attoRevoca);
        $revoca->setAttuazioneControlloRichiesta($this->richiesta->getAttuazioneControllo());

        $this->gestore->aggiornaRevoca($revoca);
        $impegni = $this->richiesta->getMonImpegni();

        $this->assertEmpty($impegni);
    }

    public function testAggiungiRevoca(): void {
        $attoRevoca = new AttoRevoca();
        $attoRevoca->setData(new \DateTime());

        $revoca = new Revoca();
        $revoca->setAttoRevoca($attoRevoca);
        $revoca->setContributoRevocato(1000);
        $revoca->setAttuazioneControlloRichiesta($this->richiesta->getAttuazioneControllo());

        $this->gestore->aggiornaRevoca($revoca);
        $impegni = $this->richiesta->getMonImpegni();

        $this->assertCount(1, $impegni);

        /** @var RichiestaImpegni $impegno */
        $impegno = $impegni->first();

        $this->assertEquals(RichiestaImpegni::DISIMPEGNO, $impegno->getTipologiaImpegno());
        $this->assertEquals(1000, $impegno->getImportoImpegno());
        $this->assertSame($revoca, $impegno->getRevoca());
        $this->assertSame($this->richiesta, $impegno->getRichiesta());
        $this->assertInstanceOf(TC38CausaleDisimpegno::class, $impegno->getTc38CausaleDisimpegno());
    }

    public function testAggiungiImpegnoAmmesso(): void {
        $attoRevoca = new AttoRevoca();
        $attoRevoca->setData(new \DateTime());

        $revoca = new Revoca();
        $revoca->setAttoRevoca($attoRevoca);
        $revoca->setContributoRevocato(1000);
        $revoca->setAttuazioneControlloRichiesta($this->richiesta->getAttuazioneControllo());

        $this->gestore->aggiornaRevoca($revoca);
        $impegni = $this->richiesta->getMonImpegni();

        $this->assertCount(1, $impegni);

        /** @var impegniAmmessi $ammesso */
        $ammesso = $impegni->first()->getMonImpegniAmmessi()->first();
        $this->assertNotFalse($ammesso, 'Impegno ammesso non presente');
        $this->assertEquals(1000, $ammesso->getImportoImpAmm());
        $this->assertEquals('D', $ammesso->getTipologiaImpAmm());
        $this->assertInstanceOf(TC38CausaleDisimpegno::class, $ammesso->getTc38CausaleDisimpegnoAmm());
    }

    public function testModificaRevoca(): void {
        $attoRevoca = new AttoRevoca();
        $data = new \DateTime();
        $attoRevoca->setData($data);

        $revoca = new Revoca();
        $revoca->setAttoRevoca($attoRevoca);
        $revoca->setContributoRevocato(1000);
        $revoca->setAttuazioneControlloRichiesta($this->richiesta->getAttuazioneControllo());

        $impegno = new RichiestaImpegni($this->richiesta, 'D');
        $this->richiesta->addMonImpegni($impegno);
        $revoca->setImpegno($impegno);

        $this->gestore->aggiornaRevoca($revoca);
        $impegni = $this->richiesta->getMonImpegni();

        $this->assertCount(1, $impegni);
        $this->assertSame($impegno, $impegni->first());
        $this->assertEquals(1000, $impegno->getImportoImpegno());
        $this->assertEquals(RichiestaImpegni::DISIMPEGNO, $impegno->getTipologiaImpegno());
    }

    public function testModificaRevocaImpegnoAmmesso(): void {
        $attoRevoca = new AttoRevoca();
        $data = new \DateTime();
        $attoRevoca->setData($data);

        $revoca = new Revoca();
        $revoca->setAttoRevoca($attoRevoca);
        $revoca->setContributoRevocato(1000);
        $revoca->setAttuazioneControlloRichiesta($this->richiesta->getAttuazioneControllo());

        $impegno = new RichiestaImpegni($this->richiesta, 'D');
        $impegno->setTc38CausaleDisimpegno(new TC38CausaleDisimpegno());
        $this->richiesta->addMonImpegni($impegno);
        $revoca->setImpegno($impegno);

        $ammesso = new ImpegniAmmessi($impegno);
        $impegno->addMonImpegniAmmessi($ammesso);

        $this->gestore->aggiornaRevoca($revoca);
        $impegni = $this->richiesta->getMonImpegni();

        $this->assertCount(1, $impegni);

        /** @var impegniAmmessi $ammesso */
        $ammesso = $impegni->first()->getMonImpegniAmmessi()->first();

        $this->assertNotFalse($ammesso, 'Impegno ammesso non presente');
        $this->assertEquals(1000, $ammesso->getImportoImpAmm());
        $this->assertEquals('D', $ammesso->getTipologiaImpAmm());
        $this->assertInstanceOf(TC38CausaleDisimpegno::class, $ammesso->getTc38CausaleDisimpegnoAmm());
    }

    public function testCancellazioneRevoca(): void {
        $attoRevoca = new AttoRevoca();
        $data = new \DateTime();
        $attoRevoca->setData($data);

        $revoca = new Revoca();
        $revoca->setAttoRevoca($attoRevoca);
        $revoca->setContributoRevocato(1000);
        $revoca->setAttuazioneControlloRichiesta($this->richiesta->getAttuazioneControllo());

        $impegno = new RichiestaImpegni($this->richiesta, 'D');
        $impegno->setTc38CausaleDisimpegno(new TC38CausaleDisimpegno());
        $this->richiesta->addMonImpegni($impegno);
        $revoca->setImpegno($impegno);

        $ammesso = new ImpegniAmmessi($impegno);
        $impegno->addMonImpegniAmmessi($ammesso);

        $this->em->expects($this->exactly(2))->method('remove')->with(
            $this->logicalOr(
                $this->isInstanceOf(RichiestaImpegni::class),
                $this->isInstanceOf(ImpegniAmmessi::class)
            )
        );

        $this->gestore->rimuoviImpegniRevoca($revoca);

        $this->assertEmpty($impegno->getMonImpegniAmmessi());
        $this->assertEmpty($this->richiesta->getMonImpegni());
    }

    public function testImpegniSbagliatiMaNonVisibili(): void {
        $p = $this->addPagamento(1000);
        $mandato = $this->setMandato($p, 1000);

        $res = $this->gestore->validaImpegniBeneficiario();

        $this->assertNotNull($res);
        $this->assertEmpty($res);
    }
}
