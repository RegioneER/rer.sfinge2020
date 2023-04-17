<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaPG00;
use MonitoraggioBundle\Repository\PG00ProcedureAggiudicazioneRepository;
use AttuazioneControlloBundle\Entity\ProceduraAggiudicazione;
use MonitoraggioBundle\Entity\PG00ProcedureAggiudicazione;
use MonitoraggioBundle\Repository\TC22MotivoAssenzaCIGRepository;
use MonitoraggioBundle\Repository\TC23TipoProceduraAggiudicazioneRepository;
use MonitoraggioBundle\Entity\TC22MotivoAssenzaCIG;
use MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione;
use MonitoraggioBundle\Exception\EsportazioneException;


class EsportaPG00Test extends EsportazioneRichiestaBase {
    /**
     * @var EsportaPG00
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaPG00($this->container);
    }

    public function testEsportazioneNonNecessaria(): void {
        $r = $this->createMock(PG00ProcedureAggiudicazioneRepository::class);
        $this->esportazioneNonNecessaria($r);
    }

    public function testEsportazioneElemento(){
        $pg = new ProceduraAggiudicazione($this->richiesta);
        $this->richiesta->addMonProcedureAggiudicazione($pg);
        $res = $this->esporta->execute($this->richiesta, $this->tavola);

        $this->assertNotEmpty($res);
        /** @var PG00ProcedureAggiudicazione $first */
        $first = $res->first();
        
        $this->assertInstanceOf(PG00ProcedureAggiudicazione::class, $first);
    }

    public function testEsportazioneVuota(){
        $res = $this->esporta->execute($this->richiesta, $this->tavola);

        $this->assertEmpty($res);
    }
    
    public function testImportazioneInputErrato(): void {
        $this->importazioneConInputNonValido();
    }

    public function testImportazioneCIG(){
        $this->setRepositories(null, null);
        $input = [
            'cod_prog',
            'proc_agg',
            '12345678',//cig
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
          
        ];
        $res = $this->esporta->importa($input);
        $this->assertInstanceOf(PG00ProcedureAggiudicazione::class, $res);
    }

    /**
     * @dataProvider getInputSenzaCIG
     */
    public function testImportazioneSenzaCIG($input){
        $tc22 = new TC22MotivoAssenzaCIG();
        $tc23 = new TC23TipoProceduraAggiudicazione();
        $this->setRepositories($tc22, $tc23);
        
        $res = $this->esporta->importa($input);

        $this->assertInstanceOf(PG00ProcedureAggiudicazione::class, $res);
    }

    public function getInputSenzaCIG(): array
    {
        return [[[
            'cod_prog',
            'proc_agg',
            '9999',//cig
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
          
        ]]];
    }

    /**
     * @dataProvider getInputSenzaCIG
     */
    public function testImportazioneSenzaCIG_E_ProcAgg($input){
        $tc22 = new TC22MotivoAssenzaCIG();
        $this->setRepositories($tc22, null);
        
        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Tipo procedura aggiudicazione non valida');

        $res = $this->esporta->importa($input);
    }

    /**
     * @dataProvider getInputSenzaCIG
     */
    public function testImportazioneSenzaCIG_E_MotivoAssenza($input){
        $tc23 = new TC23TipoProceduraAggiudicazione();
        $this->setRepositories(null, $tc23);
        
        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage('Motivo assenza CIG non valido');

        $res = $this->esporta->importa($input);
    }

    protected function setRepositories($tc22, $tc23):void
    {
        $tc22Repo = $this->createMockFindOneBy(TC22MotivoAssenzaCIGRepository::class, $tc22);
        $tc23Repo = $this->createMockFindOneBy(TC23TipoProceduraAggiudicazioneRepository::class, $tc23);
        $this->em->method('getRepository')->will(
            $this->returnValueMap([
                ['MonitoraggioBundle:TC22MotivoAssenzaCIG',$tc22Repo],
                ['MonitoraggioBundle:TC23TipoProceduraAggiudicazione',$tc23Repo],
            ])
        );
    }
}