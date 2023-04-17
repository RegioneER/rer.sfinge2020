<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\EsportaFN03;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\FN03PianoCosti;
use RichiesteBundle\Entity\VocePianoCosto;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\PianoCosto;
use MonitoraggioBundle\Service\GestorePianoCostoService;
use MonitoraggioBundle\GestoriPianoCosto\PianoCostoGenerico;
use MonitoraggioBundle\Entity\RichiestaPianoCosti;
use MonitoraggioBundle\Repository\FN03PianoCostiRepository;


class EsportaFN03Test extends EsportazioneRichiestaBase {
   
    /**
     * @var EsportaFN03
     */
    protected $esporta;

    /**
     * @var
     */
    protected $pianoCostiService;
    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        
        $this->pianoCostiService = $this->createMock(PianoCostoGenerico::class);

        $service = $this->createMock(GestorePianoCostoService::class);
        $service->method('getGestore')->willReturn($this->pianoCostiService);

        $this->container->set('gestore_voci_piano_costo_monitoraggio', $service);
        $this->esporta = new EsportaFN03($this->container);
    }

    public function testImportazioneOk(){
        $input = [
            'cod_progetto',
            2018,
            888,
            999,
            null
        ];

        $res = $this->esporta->importa($input);

        $this->assertNotNull($res);
        $this->assertInstanceOf(FN03PianoCosti::class, $res);
        $this->assertEquals('cod_progetto', $res->getCodLocaleProgetto());
        $this->assertEquals(2018, $res->getAnnoPiano());
        $this->assertEquals(888, $res->getImpRealizzato());
        $this->assertEquals(999, $res->getImpDaRealizzare());
    }
    

    public function testImportazioneErroreInput(){
        $input = [];

        $this->expectException(EsportazioneException::class);
        $this->esporta->importa($input);
    }


    public function testNessunPianoCostiDaEsportare(){
        $this->pianoCostiService->method('generaArrayPianoCostoTotaleRealizzato')->willReturn([]);
        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);
    }

    public function  testEsportazioneOk(){
        $voce1 = new RichiestaPianoCosti($this->richiesta);        
        $voce1->setAnnoPiano(2010)
        ->setImportoDaRealizzare(0)
        ->setImportoRealizzato(1000);
        $voce2 = new RichiestaPianoCosti($this->richiesta);
        $voce1->setAnnoPiano(2011)
        ->setImportoDaRealizzare(900)
        ->setImportoRealizzato(1100);
        $piano =[
            $voce1,
            $voce2
        ];
        $this->pianoCostiService->method('generaArrayPianoCostoTotaleRealizzato')->willReturn($piano);

        $res = $this->esporta->execute($this->richiesta, $this->tavola, false);

        $this->assertNotNull($res);
        $this->assertNotEmpty($res);
    }

    public function testEsportazioneNonNecessaria()
    {
        $repo = $this->createMock(FN03PianoCostiRepository::class);
        $this->esportazioneNonNecessaria($repo);
    }
}