<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaPR00;
use MonitoraggioBundle\Entity\PR00IterProgetto;
use MonitoraggioBundle\Repository\PR00IterProgettoRepository;
use RichiesteBundle\Entity\VoceFaseProcedurale;
use RichiesteBundle\Entity\FaseProcedurale;
use MonitoraggioBundle\Entity\TC46FaseProcedurale;
use RichiesteBundle\Entity\FaseNatura;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Repository\TC46FaseProceduraleRepository;


class EsportaPR00Test extends EsportazioneRichiestaBase {
    /**
     * @var EsportaPR00
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaPR00($this->container);
    }

    public function testEsportazioneNonNecessaria(): void {
        $r = $this->createMock(PR00IterProgettoRepository::class);
        $this->esportazioneNonNecessaria($r);
    }
    public function testEsportazioneVuota(){
        $res = $this->esporta->execute($this->richiesta, $this->tavola);

        $this->assertEmpty($res);
    }

    public function testEsportazioneElemento(){
        $tc46 = new TC46FaseProcedurale();
        $natura = new FaseNatura();
        $natura->setDefinizione($tc46);
        $fase = new FaseProcedurale();
        $fase->setFaseNatura($natura);
        $voce = new VoceFaseProcedurale();
        $fase->addVociFaseProcedurale($voce);
        $voce->setRichiesta($this->richiesta);
        $voce->setFaseProcedurale($fase);
        $this->richiesta->addVociFaseProcedurale($voce);

        $res = $this->esporta->execute($this->richiesta, $this->tavola);

        $this->assertNotEmpty($res);
        /** @var PR00IterProgetto $first */
        $first = $res->first();
        
        $this->assertInstanceOf(PR00IterProgetto::class, $first);
    }

    public function testEsportazioneElementoSenzaDefinizione(){
        // $tc46 = new TC46FaseProcedurale();
        $natura = new FaseNatura();
        // $natura->setDefinizione($tc46);
        $fase = new FaseProcedurale();
        $fase->setFaseNatura($natura);
        $voce = new VoceFaseProcedurale();
        $fase->addVociFaseProcedurale($voce);
        $voce->setRichiesta($this->richiesta);
        $voce->setFaseProcedurale($fase);
        $this->richiesta->addVociFaseProcedurale($voce);

        $this->expectException(EsportazioneException::class);
        $this->expectExceptionMessage("Definizione fase procedura mancancante");

        $res = $this->esporta->execute($this->richiesta, $this->tavola);
    }
    

    public function testImportazioneInputErrato(): void {
        $this->importazioneConInputNonValido();
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneSenzaFaseProcedurale(array $input):void
    {
        $repo = $this->createMockFindOneBy(TC46FaseProceduraleRepository::class, null);
        $this->em->method('getRepository')->willReturn($repo);
        
        $this->expectException(EsportazioneException::class);

        $this->esporta->importa($input);

    }

    public function getInput(): array
    {
        return [[[
            'cod_progetto',
            'tc46',
            '02/01/2001',
            '01/01/2001',
            '01/01/2010',
            NULL,
            '',
        ]]];
    }

     /**
     * @dataProvider getInput
     */
    public function testImportazioneConSuccesso(array $input):void
    {
        $tc46 = new TC46FaseProcedurale();
        $repo = $this->createMockFindOneBy(TC46FaseProceduraleRepository::class, $tc46);
        $this->em->method('getRepository')->willReturn($repo);
        
        $res = $this->esporta->importa($input);

        $this->assertInstanceOf(PR00IterProgetto::class, $res);
        $this->assertSame($tc46, $res->getTc46FaseProcedurale());
        $this->assertEquals(new \DateTime('2001-01-02'), $res->getDataInizioPrevista());
        $this->assertEquals(new \DateTime('2001-01-01'), $res->getDataInizioEffettiva());
        $this->assertEquals(new \DateTime('2010-01-01'), $res->getDataFinePrevista());
        $this->assertNull($res->getDataFineEffettiva());

    }
}