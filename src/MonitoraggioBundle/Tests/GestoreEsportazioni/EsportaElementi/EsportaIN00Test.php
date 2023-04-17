<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaIN00;
use MonitoraggioBundle\Repository\IN00IndicatoriRisultatoRepository;
use RichiesteBundle\Entity\IndicatoreRisultato;
use MonitoraggioBundle\Entity\IN00IndicatoriRisultato;
use MonitoraggioBundle\Entity\TC42IndicatoriRisultatoComuni;
use MonitoraggioBundle\Entity\TC43IndicatoriRisultatoProgramma;
use MonitoraggioBundle\Repository\TC42IndicatoriRisultatoComuniRepository;
use MonitoraggioBundle\Repository\TC43IndicatoriRisultatoProgrammaRepository;
use MonitoraggioBundle\Exception\EsportazioneException;

class EsportaIN00Test extends EsportazioneRichiestaBase {
    /**
     * @var EsportaIN00
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaIN00($this->container);
    }

    public function testEsportazioneNonNecessaria(): void {
        $r = $this->createMock(IN00IndicatoriRisultatoRepository::class);
        $this->esportazioneNonNecessaria($r);
    }

    public function testEsportazioneConSuccesso(): void {
        $tc = new TC42IndicatoriRisultatoComuni();
        $indicatore = new IndicatoreRisultato($this->richiesta, $tc);
        $this->richiesta->addMonIndicatoreRisultato($indicatore);
        $res = $this->esporta->execute($this->richiesta, $this->tavola);
        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
        /** @var IN00IndicatoriRisultato $first */
        $first = $res->first();
        $this->assertNotFalse($first);
        $this->assertInstanceOf(IN00IndicatoriRisultato::class, $first);
        $this->assertSame($tc, $first->getIndicatoreId());
        $this->assertEquals('COM', $first->getTipoIndicatoreDiRisultato());
    }

    public function testEsportazioneTipoDPR(): void {
        $tc = new TC43IndicatoriRisultatoProgramma();
        $indicatore = new IndicatoreRisultato($this->richiesta, $tc);
        $this->richiesta->addMonIndicatoreRisultato($indicatore);
        $res = $this->esporta->execute($this->richiesta, $this->tavola);
        /** @var IN00IndicatoriRisultato $first */
        $first = $res->first();
        $this->assertEquals('DPR', $first->getTipoIndicatoreDiRisultato());
    }

    public function testEsportazioneSenzaRisultato(): void {
        $res = $this->esporta->execute($this->richiesta, $this->tavola);
        $this->assertNotNull($res);
        $this->assertEmpty($res);
    }

    public function testImportazioneInputErrato(): void {
        $this->importazioneConInputNonValido();
    }

    public function testImportazioneIndicatoreComune(): void {
        $tc42 = new TC42IndicatoriRisultatoComuni();
        $repo = $this->createMockFindOneBy(TC42IndicatoriRisultatoComuniRepository::class, $tc42);
        $this->em->method('getRepository')->with('MonitoraggioBundle:TC42IndicatoriRisultatoComuni')->willReturn($repo);
        $input = [
                'cod_progetto',
                'COM',
                'id_idncatore',
                '',
            ];
        $res = $this->esporta->importa($input);

        $this->assertInstanceOf(IN00IndicatoriRisultato::class, $res);
        $this->assertSame($tc42, $res->getIndicatoreId());
        $this->assertEquals('COM', $res->getTipoIndicatoreDiRisultato());
    }

    public function testImportazioneIndicatoreProgramma(): void {
        $tc43 = new TC43IndicatoriRisultatoProgramma();
        $repo = $this->createMockFindOneBy(TC43IndicatoriRisultatoProgrammaRepository::class, $tc43);
        $this->em->method('getRepository')->with('MonitoraggioBundle:TC43IndicatoriRisultatoProgramma')->willReturn($repo);
        $input = [
                'cod_progetto',
                'DPR',
                'id_idncatore',
                '',
            ];
        $res = $this->esporta->importa($input);

        $this->assertInstanceOf(IN00IndicatoriRisultato::class, $res);
        $this->assertSame($tc43, $res->getIndicatoreId());
        $this->assertEquals('DPR', $res->getTipoIndicatoreDiRisultato());
    }

    public function testImportazioneSenzaIndicatore(): void {
        $repo = $this->createMockFindOneBy(TC43IndicatoriRisultatoProgrammaRepository::class, null);
        $this->em->method('getRepository')->willReturn($repo);
        $input = [
                'cod_progetto',
                'DPR',
                'id_idncatore',
                '',
            ];

        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Indicatore risultato non valido');

        $res = $this->esporta->importa($input);
    }
}
