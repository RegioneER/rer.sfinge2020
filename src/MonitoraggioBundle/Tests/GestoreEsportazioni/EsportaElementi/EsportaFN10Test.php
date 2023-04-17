<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaFN10;
use MonitoraggioBundle\Repository\FN10EconomieRepository;
use AttuazioneControlloBundle\Entity\Economia;
use MonitoraggioBundle\Entity\FN10Economie;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;
use MonitoraggioBundle\Repository\TC33FonteFinanziariaRepository;
use MonitoraggioBundle\Exception\EsportazioneException;

class EsportaFN10Test extends EsportazioneRichiestaBase {
    /**
     * @var EsportaFN10
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaFN10($this->container);
    }

    public function testEsportazioneNonNecessaria(): void {
        $r = $this->createMock(FN10EconomieRepository::class);
        $this->esportazioneNonNecessaria($r);
    }

    public function testEsportazioneConSuccesso(): void {
        $economia = new Economia();
        $tc33 = new TC33FonteFinanziaria();
        $economia->setImporto(999)
        ->setTc33FonteFinanziaria($tc33);
        $this->richiesta->addMonEconomie($economia);
        $res = $this->esporta->execute($this->richiesta, $this->tavola);

        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
        /** @var FN10Economie $first */
        $first = $res->first();
        $this->assertEquals(999, $first->getImporto());
        $this->assertNull($first->getFlgCancellazione());
        $this->assertSame($tc33, $first->getTc33FonteFinanziaria());
    }

    public function testImportazioneInputErrato(): void {
        $this->importazioneConInputNonValido();
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneConSuccesso(array $input): void {
        $tc33 = new TC33FonteFinanziaria();
        $repo = $this->createMockFindOneBy(TC33FonteFinanziariaRepository::class, $tc33);
        $this->em->method('getRepository')->willreturn($repo);

        $res = $this->esporta->importa($input);
        $this->assertNotNull($res);
        $this->assertInstanceOf(FN10Economie::class, $res);
        $this->assertSame($tc33, $res->getTc33FonteFinanziaria());
    }

    public function getInput(): array {
        return [[[
            'cod_progetto',
            'tc33',
            '999.99',
            '',
        ]]];
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneSenzaFonteFinanziaria($input) {
        $repo = $this->createMockFindOneBy(TC33FonteFinanziariaRepository::class, null);
        $this->em->method('getRepository')->willreturn($repo);

        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Fonte finanziaria non valida');

        $res = $this->esporta->importa($input);
    }
}
