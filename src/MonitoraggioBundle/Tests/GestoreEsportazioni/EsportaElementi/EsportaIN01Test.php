<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\Repository\IN01IndicatoriOutputRepository;
use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaIN01;
use RichiesteBundle\Entity\IndicatoreOutput;
use MonitoraggioBundle\Entity\IN01IndicatoriOutput;
use MonitoraggioBundle\Entity\TC44IndicatoriOutputComuni;
use MonitoraggioBundle\Entity\TC45IndicatoriOutputProgramma;
use MonitoraggioBundle\Repository\TC44IndicatoriOutputComuniRepository;
use MonitoraggioBundle\Repository\TC45IndicatoriOutputProgrammaRepository;
use MonitoraggioBundle\Exception\EsportazioneException;

class EsportaIN01Test extends EsportazioneRichiestaBase {
    /**
     * @var EsportaIN01
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaIN01($this->container);
    }

    public function testEsportazioneNonNecessaria(): void {
        $r = $this->createMock(IN01IndicatoriOutputRepository::class);
        $this->esportazioneNonNecessaria($r);
    }

    public function testEsportazioneOkComune() {
        $tc44 = new TC44IndicatoriOutputComuni();
        $indicatore = new IndicatoreOutput($this->richiesta, $tc44);
        $indicatore
            ->setValoreRealizzato(1)
            ->setValoreValidato(2)
            ->setValProgrammato(5);
        $this->richiesta->addMonIndicatoreOutput($indicatore);
        $res = $this->esporta->execute($this->richiesta, $this->tavola);

        $this->assertNotEmpty($res);
        /** @var IN01IndicatoriOutput $first */
        $first = $res->first();
        $this->assertEquals('COM', $first->getTipoIndicatoreDiOutput());
        $this->assertSame($tc44, $first->getIndicatoreId());
        $this->assertEquals(5, $first->getValProgrammato());
        $this->assertEquals(2, $first->getValoreRealizzato());
    }

    public function testEsportazioneOkProgramma() {
        $tc44 = new TC45IndicatoriOutputProgramma();
        $indicatore = new IndicatoreOutput($this->richiesta, $tc44);
        $indicatore
            ->setValoreRealizzato(1)
            ->setValoreValidato(2)
            ->setValProgrammato(5);
        $this->richiesta->addMonIndicatoreOutput($indicatore);
        $res = $this->esporta->execute($this->richiesta, $this->tavola);

        $this->assertNotEmpty($res);
        /** @var IN01IndicatoriOutput $first */
        $first = $res->first();
        $this->assertEquals('DPR', $first->getTipoIndicatoreDiOutput());
        $this->assertSame($tc44, $first->getIndicatoreId());
    }

    public function testEsportazioneNessunElemento() {
        $res = $this->esporta->execute($this->richiesta, $this->tavola);

        $this->assertEmpty($res);
    }

    public function testImportazioneInputErrato(): void {
        $this->importazioneConInputNonValido();
    }

    /**
     * @dataProvider getInputComune
     */
    public function testImportazioneComune(array $input): void {
        $tc44 = new TC44IndicatoriOutputComuni();
        $repo = $this->createMockFindOneBy(TC44IndicatoriOutputComuniRepository::class, $tc44);
        $this->em->method('getRepository')->with('MonitoraggioBundle:TC44IndicatoriOutputComuni')->wilLReturn($repo);
        $res = $this->esporta->importa($input);

        $this->assertInstanceOf(IN01IndicatoriOutput::class, $res);
        $this->assertEquals(1, $res->getValProgrammato());
        $this->assertEquals(2, $res->getValoreRealizzato());
        $this->assertEquals('COM', $res->getTipoIndicatoreDiOutput());
        $this->assertSame($tc44, $res->getIndicatoreId());
    }

    public function testImportazioneProgramma(): void {
        $input = [
            'cod_progetto',
            'DPR',
            'tc',
            '1',
            '2',
            ''
        ];

        $tc44 = new TC45IndicatoriOutputProgramma();
        $repo = $this->createMockFindOneBy(TC45IndicatoriOutputProgrammaRepository::class, $tc44);
        $this->em->method('getRepository')->with('MonitoraggioBundle:TC45IndicatoriOutputProgramma')->wilLReturn($repo);
        $res = $this->esporta->importa($input);

        $this->assertInstanceOf(IN01IndicatoriOutput::class, $res);
        $this->assertEquals('DPR', $res->getTipoIndicatoreDiOutput());
        $this->assertSame($tc44, $res->getIndicatoreId());
    }

    /**
     * @dataProvider getInputComune
     */
    public function testImportazioneSenzaIndicatore(array $input):void{
        $repo = $this->createMockFindOneBy(TC44IndicatoriOutputComuniRepository::class, null);
        $this->em->method('getRepository')->with('MonitoraggioBundle:TC44IndicatoriOutputComuni')->wilLReturn($repo);

        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Indicatore output non valido');

        $res = $this->esporta->importa($input);
    }

    public function getInputComune(): array{
        return [[[
            'cod_progetto',
            'COM',
            'tc',
            '1',
            '2',
            ''
        ]]];
    }
}
