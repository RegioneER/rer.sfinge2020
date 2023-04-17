<?php

namespace RichiesteBundle\Tests\Entity;

use RichiesteBundle\Entity\Richiesta;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\Collection;
use AttuazioneControlloBundle\Entity\Finanziamento;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;

class RichiestaTest extends TestCase {
    /**
     * @var Richiesta
     */
    protected $richiesta;

    public function setUp() {
        $this->richiesta = new Richiesta();
    }

    public function testFinanziamentoNessunFinaziamento(): void {
        $res = $this->richiesta->getMonFinanziamenti();
        $this->assertInstanceOf(Collection::class, $res);
        $this->assertEmpty($res);
    }

    public function testFinanziamentoTuttiRisultati(): void {
        $this->aggiungiFinanziamento(null);

        $res = $this->richiesta->getMonFinanziamenti();

        $this->assertInstanceOf(Collection::class, $res);
        $this->assertContainsOnly(Finanziamento::class, $res);
        $this->assertCount(1, $res);
    }

    public function testFinanziamentoFiltroFonte(): void {
        $fin1 = $this->aggiungiFinanziamento(new TC33FonteFinanziaria('1'));
        $fin1 = $this->aggiungiFinanziamento(new TC33FonteFinanziaria('2'));

        $res = $this->richiesta->getMonFinanziamenti('1');

        $this->assertNotEmpty($res);
        $this->assertContainsOnly(Finanziamento::class, $res);
        /** @var Finanziamento $finanziamento */
        $finanziamento = $res->first();
        $tc33 = $finanziamento->getTc33FonteFinanziaria();
        $this->assertNotNull($tc33);
        $this->assertInstanceOf(TC33FonteFinanziaria::class, $tc33);
        $this->assertSame('1', $tc33->getCodFondo());
    }

    protected function aggiungiFinanziamento(?TC33FonteFinanziaria $fonte): Finanziamento {
        $fin = new Finanziamento($this->richiesta);
        $fin->setTc33FonteFinanziaria($fonte);

        $this->richiesta->addMonFinanziamenti($fin);

        return $fin;
    }

    public function testTotaleImportoImpegni(): void{
        $impegno1 = new RichiestaImpegni($this->richiesta);
        $impegno1->setTipologiaImpegno(RichiestaImpegni::IMPEGNO)
        ->setImportoImpegno(1000);
        $this->richiesta->addMonImpegni($impegno1);

        $impegno2 = new RichiestaImpegni($this->richiesta);
        $impegno2->setTipologiaImpegno(RichiestaImpegni::IMPEGNO)
        ->setImportoImpegno(3000);
        $this->richiesta->addMonImpegni($impegno2);

        $res = $this->richiesta->getTotaleImportoImpegni();

        $this->assertNotNull($res);
        $this->assertEquals(4000, $res, '', 0.001);
    }

    public function testTotaleImportoImpegniConDisimpegni(): void{
        

        $impegno2 = new RichiestaImpegni($this->richiesta);
        $impegno2->setTipologiaImpegno(RichiestaImpegni::IMPEGNO)
        ->setImportoImpegno(3000);
        $this->richiesta->addMonImpegni($impegno2);

        $impegno1 = new RichiestaImpegni($this->richiesta);
        $impegno1->setTipologiaImpegno(RichiestaImpegni::DISIMPEGNO)
        ->setImportoImpegno(1000);
        $this->richiesta->addMonImpegni($impegno1);
        $res = $this->richiesta->getTotaleImportoImpegni();

        $this->assertNotNull($res);
        $this->assertEquals(2000, $res, '', 0.001);
    }

    public function testTotaleImportoSenzaImpegni(): void{
        $res = $this->richiesta->getTotaleImportoImpegni();

        $this->assertNotNull($res);
        $this->assertEquals(0.0, $res, '');
    }
}
