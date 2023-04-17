<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaTR00;
use MonitoraggioBundle\Repository\TR00TrasferimentiRepository;
use MonitoraggioBundle\Entity\Trasferimento;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTrasferimento;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;
use MonitoraggioBundle\Entity\TR00Trasferimenti;
use SoggettoBundle\Entity\Azienda;
use SoggettoBundle\Entity\FormaGiuridica;
use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Entity\TC49CausaleTrasferimento;
use MonitoraggioBundle\Repository\TC4ProgrammaRepository;
use MonitoraggioBundle\Repository\TC49CausaleTrasferimentoRepository;

class EsportaTR00Test extends EsportazioneBase {
    /**
     * @var EsportaTR00
     */
    protected $esporta;

    /**
     * @var Trasferimento
     */
    protected $trasferimento;

    /**
     * @var MonitoraggioConfigurazioneEsportazioneTavole
     */
    protected $tavola;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaTR00($this->container);
        $this->trasferimento = new Trasferimento();
        $configurazione = new MonitoraggioConfigurazioneEsportazioneTrasferimento($this->trasferimento);
        $this->tavola = new MonitoraggioConfigurazioneEsportazioneTavole($configurazione);
    }

    public function testEsportazioneNonNecessaria(): void {
        $r = $this->createMock(TR00TrasferimentiRepository::class);
        $this->em->method('getRepository')->willReturn($r);

        $this->expectException(EsportazioneException::class);

        $this->esporta->execute($this->trasferimento, $this->tavola, true);
    }

    public function testEsportazione(): void {
        $soggetto = new Azienda();
        $soggetto->setCodiceFiscale('codice_fiscale');
        $forma = new FormaGiuridica();
        $forma->setSoggettoPubblico(true);
        $soggetto->setFormaGiuridica($forma);
        $this->trasferimento->setSoggetto($soggetto);
        $res = $this->esporta->execute($this->trasferimento, $this->tavola, false);

        $this->assertInstanceOf(TR00Trasferimenti::class, $res);
        $this->assertEquals('CODICE_FISCALE', $res->getCfSogRicevente());
        $this->assertEquals('S', $res->getFlagSoggettoPubblico());
    }

    public function testEsportazioneSenzaSoggetto(): void {
        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Soggetto non definito per il trasferimento');

        $res = $this->esporta->execute($this->trasferimento, $this->tavola, false);
    }

    public function testEsportazioneSenzaFormaGiuridica() {
        $soggetto = new Azienda();
        $soggetto->setCodiceFiscale('codice_fiscale');
        $this->trasferimento->setSoggetto($soggetto);

        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Forma giuridica non definito per il soggetto');

        $res = $this->esporta->execute($this->trasferimento, $this->tavola, false);
    }

    public function testImportazioneInputErrato() {
        $this->expectException(EsportazioneException::class);

        $this->esporta->importa([]);
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneConSuccesso(array $input): void {
        $tc4 = new TC4Programma();
        $tc49 = new TC49CausaleTrasferimento();

        $repoTC4 = $this->createMockFindOneBy(TC4ProgrammaRepository::class, $tc4);
        $repoTC49 = $this->createMockFindOneBy(TC49CausaleTrasferimentoRepository::class, $tc49);

        $this->em->method('getRepository')->will(
            $this->returnValueMap([
                ['MonitoraggioBundle:TC4Programma', $repoTC4],
                ['MonitoraggioBundle:TC49CausaleTrasferimento', $repoTC49],
            ])
            );

        $res = $this->esporta->importa($input);

        $this->assertNotNull($res);
        $this->assertInstanceOf(TR00Trasferimenti::class, $res);
        $this->assertSame($tc4, $res->getTc4Programma());
        $this->assertEquals('CODICE_FISCALE', $res->getCfSogRicevente());
    }

    public function getInput(): array {
        return [[[
            'cod_trasf',
            '01/01/1991',
            'prog',
            'causale',
            '999',
            'CODICE_FISCALE',
            'S',
            '',
        ]]];
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneSenzaProgramma(array $input): void {
        $tc4 = null;
        $tc49 = new TC49CausaleTrasferimento();

        $repoTC4 = $this->createMockFindOneBy(TC4ProgrammaRepository::class, $tc4);
        $repoTC49 = $this->createMockFindOneBy(TC49CausaleTrasferimentoRepository::class, $tc49);

        $this->em->method('getRepository')->will(
            $this->returnValueMap([
                ['MonitoraggioBundle:TC4Programma', $repoTC4],
                ['MonitoraggioBundle:TC49CausaleTrasferimento', $repoTC49],
            ])
            );

        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->importa($input);
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneSenzaCausale(array $input): void {
        $tc4 = new TC4Programma();
        $tc49 = null;

        $repoTC4 = $this->createMockFindOneBy(TC4ProgrammaRepository::class, $tc4);
        $repoTC49 = $this->createMockFindOneBy(TC49CausaleTrasferimentoRepository::class, $tc49);

        $this->em->method('getRepository')->will(
            $this->returnValueMap([
                ['MonitoraggioBundle:TC4Programma', $repoTC4],
                ['MonitoraggioBundle:TC49CausaleTrasferimento', $repoTC49],
            ])
            );

        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->importa($input);
    }
}
