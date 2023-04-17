<?php

namespace MonitoraggioBundle\Tests\Controller;

use BaseBundle\Tests\Service\TestBaseService;
use MonitoraggioBundle\Controller\ImportazioniController;
use Symfony\Component\HttpFoundation\Response;
use MonitoraggioBundle\GestoriEsportazione\IEstrattoreStruttura;
use MonitoraggioBundle\Service\GestoreEsportazioneStruttureService;

class UImportazioniControllerTest extends TestBaseService {
    /**
     * @var ImportazioniController
     */
    protected $controller;

    public function setUp() {
        parent::setUp();
        $this->controller = new ImportazioniController();
        $this->controller->setContainer($this->container);
    }

    public function testEstrazioneStrutture() {
        $service = $this->createMock(GestoreEsportazioneStruttureService::class);
        $this->container->set('monitoraggio.esportazione_strutture', $service);
        $ap00 = $this->createMock(IEstrattoreStruttura::class);
        $service->method('getGestore')->with('AP00')->willReturn($ap00);
        $response = new Response();
        $ap00->expects($this->once())->method('generateResult')->willReturn($response);

        $res = $this->controller->estrazioneStruttureAction('AP00');

        $this->assertSame($response, $res);
    }
}
