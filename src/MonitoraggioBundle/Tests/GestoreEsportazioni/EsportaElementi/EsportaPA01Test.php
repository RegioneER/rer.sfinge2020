<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaPA01;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneProcedura;
use SfingeBundle\Entity\Bando;
use MonitoraggioBundle\Repository\PA01ProgrammiCollegatiProceduraAttivazioneRepository;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\TC1ProceduraAttivazione;
use SfingeBundle\Entity\ProgrammaProcedura;
use MonitoraggioBundle\Entity\TC4Programma;
use MonitoraggioBundle\Entity\PA01ProgrammiCollegatiProceduraAttivazione;
use MonitoraggioBundle\Repository\TC4ProgrammaRepository;

class EsportaPA01Test extends EsportazioneBase {
    /**
     * @var EsportaPA01
     */
    protected $esporta;

    /**
     * @var Bando
     */
    protected $procedura;

    /**
     * @var MonitoraggioConfigurazioneEsportazioneTavole
     */
    protected $tavola;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaPA01($this->container);
        $this->procedura = new Bando();
        $configurazione = new MonitoraggioConfigurazioneEsportazioneProcedura($this->procedura);
        $this->tavola = new MonitoraggioConfigurazioneEsportazioneTavole($configurazione);
    }

    public function testEsportazioneNonNecessaria(): void {
        $r = $this->createMock(PA01ProgrammiCollegatiProceduraAttivazioneRepository::class);
        $this->em->method('getRepository')->willReturn($r);

        $this->expectException(EsportazioneException::class);

        $this->esporta->execute($this->procedura, $this->tavola, true);
    }

    public function testEsportazioneConSuccesso(): void {
        $tc1 = new TC1ProceduraAttivazione();
        $tc1->setCodProcAtt('cod_proc_att');
        $this->procedura->setMonProcAtt($tc1);
        $tc4 = new TC4Programma();
        $programma = new ProgrammaProcedura($this->procedura, $tc4);
        $this->procedura->addMonProcedureProgrammi($programma);

        $res = $this->esporta->execute($this->procedura, $this->tavola);

        $this->assertInstanceOf(PA01ProgrammiCollegatiProceduraAttivazione::class, $res);
        $this->assertSame($tc4, $res->getTc4Programma());
        $this->assertEquals('cod_proc_att', $res->getCodProcAtt());
    }

    public function testEsportazioneSenzaProcAtt(): void {
        $tc4 = new TC4Programma();
        $programma = new ProgrammaProcedura($this->procedura, $tc4);
        $this->procedura->addMonProcedureProgrammi($programma);

        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->execute($this->procedura, $this->tavola);
    }

    public function testEsportazioneSenzaProgramma(): void {
        $tc1 = new TC1ProceduraAttivazione();
        $tc1->setCodProcAtt('cod_proc_att');
        $this->procedura->setMonProcAtt($tc1);
        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->execute($this->procedura, $this->tavola);
    }

    public function testImportazioneInputNonValido(): void {
        $this->importazioneConInputNonValido();
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneConSuccesso(array $input): void {
        $tc4 = new TC4Programma();
        $this->mockTC4($tc4);

        $res = $this->esporta->importa($input);

        $this->assertNotNull($res);
        $this->assertInstanceOf(PA01ProgrammiCollegatiProceduraAttivazione::class, $res);
    }

    public function getInput(): array {
        return [[[
            'cod_proc',
            'programma',
            '99999',
            '',
        ]]];
    }

    protected function mockTC4(?TC4Programma $tc4): void {
        $repo = $this->createMockFindOneBy(TC4ProgrammaRepository::class, $tc4);
        $this->em->method('getRepository')->willReturn($repo);
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneSenzaProgramma(array $input): void {
        $this->mockTC4(null);

        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->importa($input);
    }
}
