<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaPR01;
use MonitoraggioBundle\Repository\PR01StatoAttuazioneProgettoRepository;
use AttuazioneControlloBundle\Entity\RichiestaStatoAttuazioneProgetto;
use MonitoraggioBundle\Entity\PR01StatoAttuazioneProgetto;
use MonitoraggioBundle\Entity\TC47StatoProgetto;
use MonitoraggioBundle\Repository\TC47StatoProgettoRepository;
use MonitoraggioBundle\Exception\EsportazioneException;


class EsportaPR01Test extends EsportazioneRichiestaBase {
    /**
     * @var EsportaPR01
     */
    protected $esporta;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->esporta = new EsportaPR01($this->container);
    }

    public function testEsportazioneNonNecessaria(): void {
        $r = $this->createMock(PR01StatoAttuazioneProgettoRepository::class);
        $this->esportazioneNonNecessaria($r);
    }
    
    public function testEsportazioneVuota(){
        $res = $this->esporta->execute($this->richiesta, $this->tavola);

        $this->assertEmpty($res);
    }

    public function testEsportazioneConSuccesso()
    {
        $stato = new RichiestaStatoAttuazioneProgetto($this->richiesta);
        $this->richiesta->addMonStatoProgetti($stato);
        $tc47 = new TC47StatoProgetto();
        $stato->setStatoProgetto($tc47);

        $res = $this->esporta->execute($this->richiesta, $this->tavola);

        $this->assertNotEmpty($res);
        /** @var PR01StatoAttuazioneProgetto $first */
        $first = $res->first();
        $this->assertSame($tc47, $first->getTc47StatoProgetto());
        $this->assertEquals($this->richiesta->getProtocollo(), $first->getCodLocaleProgetto());
    }


    public function testImportazioneInputErrato(): void {
        $this->importazioneConInputNonValido();
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneConSuccesso(array $input){
        $tc47 = new TC47StatoProgetto();
        $repo = $this->createMockFindOneBy(TC47StatoProgettoRepository::class, $tc47);
        $this->em->method('getRepository')->willReturn($repo);

        $res = $this->esporta->importa($input);

        $this->assertNotNull($res);
        $this->assertInstanceOf(PR01StatoAttuazioneProgetto::class, $res);
        $this->assertEquals(new \DateTime('2011-01-01'), $res->getDataRiferimento());
        $this->assertSame($tc47, $res->getTc47StatoProgetto());
    }

    public function getInput():array{
        return [[[
            'cod_locale',
            'tc47',
            '01/01/2011',
            '',
        ]]];
    }

    /**
     * @dataProvider getInput
     */
    public function testImportazioneSenzaStatoProgetto(array $input):void{
        $repo = $this->createMockFindOneBy(TC47StatoProgettoRepository::class, null);
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->importa($input);
    } 
}