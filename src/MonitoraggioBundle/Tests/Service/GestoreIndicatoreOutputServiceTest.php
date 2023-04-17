<?php

namespace MonitoraggioBundle\Tests\Service;

use BaseBundle\Tests\Service\TestBaseService;
use MonitoraggioBundle\Service\GestoreIndicatoreService;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Service\IGestoreIndicatoreOutput;
use SfingeBundle\Entity\Bando;

class GestoreIndicatoreOutputServiceTest extends TestBaseService
{
    /**
     * @var GestoreIndicatoreService
     */
    protected $service;

    public function setUp(){
        parent::setUp();
        $this->service = new GestoreIndicatoreService($this->container);
    }
    public function testGetgestore(){
        $richiesta = new Richiesta();
        $procedura = new Bando();
        $richiesta->setProcedura($procedura);
        
        $res = $this->service->getGestore($richiesta);

        $this->assertNotNull($res);
        $this->assertInstanceOf(IGestoreIndicatoreOutput::class, $res);
    }
}