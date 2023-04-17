<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaPA01;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneProcedura;
use SfingeBundle\Entity\Bando;
use MonitoraggioBundle\Repository\PA01ProgrammiCollegatiProceduraAttivazioneRepository;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaPA00;
use MonitoraggioBundle\Repository\PA00ProcedureAttivazioneRepository;
use MonitoraggioBundle\Entity\PA00ProcedureAttivazione;
use SfingeBundle\Entity\TipoAmministrazioneEmittente;
use MonitoraggioBundle\Entity\TC1ProceduraAttivazione;
use MonitoraggioBundle\Repository\TC2TipoProceduraAttivazioneRepository;
use MonitoraggioBundle\Repository\TC3ResponsabileProceduraRepository;
use MonitoraggioBundle\Entity\TC2TipoProceduraAttivazione;
use MonitoraggioBundle\Entity\TC3ResponsabileProcedura;
use MonitoraggioBundle\Repository\TC1ProceduraAttivazioneRepository;


class EsportaPA00Test extends EsportazioneBase {
    /**
     * @var EsportaPA00
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
        $this->esporta = new EsportaPA00($this->container);
        $this->procedura = new Bando();
        $configurazione = new MonitoraggioConfigurazioneEsportazioneProcedura($this->procedura);
        $this->tavola = new MonitoraggioConfigurazioneEsportazioneTavole($configurazione);
    }

    public function testEsportazioneNonNecessaria(): void {
        $r = $this->createMock(PA00ProcedureAttivazioneRepository::class);
        $this->em->method('getRepository')->willReturn($r);

        $this->expectException(EsportazioneException::class);

        $this->esporta->execute($this->procedura, $this->tavola, true);
    }

    public function testEsportazioneConSuccesso()
    {
        $procAtt = new TC1ProceduraAttivazione();
        $this->procedura->setMonProcAtt($procAtt);
        
        $amministrazione = new TipoAmministrazioneEmittente();
        $this->procedura->setAmministrazioneEmittente($amministrazione);

        $res = $this->esporta->execute($this->procedura, $this->tavola);

        $this->assertNotNull($res);
        $this->assertInstanceOf(PA00ProcedureAttivazione::class, $res);
    }

    public function testEsportazioneSenzaProceduraAttivazione()
    {
        $amministrazione = new TipoAmministrazioneEmittente();
        $this->procedura->setAmministrazioneEmittente($amministrazione);

        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->execute($this->procedura, $this->tavola);
    }

    public function testEsportazioneSenzaAmministrazione()
    {
        $procAtt = new TC1ProceduraAttivazione();
        $this->procedura->setMonProcAtt($procAtt);
        
        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->execute($this->procedura, $this->tavola);
    }
    
    public function testImportazioneInputNonValido()
    {
       $this->importazioneConInputNonValido();
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneConSuccesso(array $input):void
    {
        $tc1 = new TC1ProceduraAttivazione();
        $tc2 = new TC2TipoProceduraAttivazione();
        $tc3 = new TC3ResponsabileProcedura();
        $this->setupRepositories($tc1, $tc2, $tc3);

        $res = $this->esporta->importa($input);

        $this->assertNotNull($res);
        $this->assertInstanceOf(PA00ProcedureAttivazione::class, $res);
    }

    public function getInput():array{
        return [[[
            'proc_att',
            'proc_att_loc',
            'rna',
            'tipo_proc_att',
            'S',
            'descrizione',
            'tipo_responsabile',
            'responsabile',
            '01/01/1991',
            '10/10/2010',
            '',
        ]]];
    }

    protected function setupRepositories(?TC1ProceduraAttivazione $tc1, ?TC2TipoProceduraAttivazione $tc2, ?TC3ResponsabileProcedura $tc3): void{
        $repoTC2 = $this->createMockFindOneBy(TC2TipoProceduraAttivazioneRepository::class, $tc2);
        $repoTC3 = $this->createMockFindOneBy(TC3ResponsabileProceduraRepository::class, $tc3);
        $repoTC1 = $this->createMockFindOneBy(TC1ProceduraAttivazioneRepository::class, $tc1);

        $this->em->method('getRepository')->will(
            $this->returnValueMap([
                ['MonitoraggioBundle:TC2TipoProceduraAttivazione', $repoTC2],
                ['MonitoraggioBundle:TC3ResponsabileProcedura', $repoTC3],
                ['MonitoraggioBundle:TC1ProceduraAttivazione', $repoTC1],
            ])
        );
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneSenzaTipoProceduraAttivazione(array $input):void
    {
        $tc1 = new TC1ProceduraAttivazione();
        $tc3 = new TC3ResponsabileProcedura();
        $this->setupRepositories($tc1, null, $tc3);

        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->importa($input);
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneSenzaResponsabileProcedura(array $input):void
    {
        $tc1 = new TC1ProceduraAttivazione();
        $tc2 = new TC2TipoProceduraAttivazione();
        $this->setupRepositories($tc1, $tc2, null);

        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->importa($input);
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneConInserimentoNuovaProceduraAttivazione(array $input):void
    {
        $tc2 = new TC2TipoProceduraAttivazione();
        $tc3 = new TC3ResponsabileProcedura();
        $this->setupRepositories(null, $tc2, $tc3);

        $this->em->expects($this->atLeastOnce())->method('persist')->with(
            $this->isInstanceOf(TC1ProceduraAttivazione::class)
        );
        $this->em->expects($this->once())->method('flush');

        $res = $this->esporta->importa($input);

        $this->assertNotNull($res);
        $this->assertInstanceOf(PA00ProcedureAttivazione::class, $res);
    }
}