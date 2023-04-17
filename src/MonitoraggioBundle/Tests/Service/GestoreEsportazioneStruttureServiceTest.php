<?php

namespace MonitoraggioBundle\Tests\Service;

use BaseBundle\Tests\Service\TestBaseService;
use MonitoraggioBundle\Service\GestoreEsportazioneStruttureService;


class GestoreEsportazioneStruttureServiceTest extends TestBaseService
{
	/** @var GestoreEsportazioneStruttureService */
	protected $service ;

	public function setUp(){
		parent::setUp();
		$this->service = new GestoreEsportazioneStruttureService($this->container);
	}

	public function testGetStruttureEsportabili(): void {
		$res = $this->service->getStruttureEsportabili();

		$this->assertNotNull($res);

	}
}