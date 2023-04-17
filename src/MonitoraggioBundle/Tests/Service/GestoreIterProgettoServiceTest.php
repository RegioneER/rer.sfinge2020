<?php

namespace MonitoraggioBundle\Tests\Service;

use BaseBundle\Tests\Service\TestBaseService;
use MonitoraggioBundle\Service\GestoreIterProgettoService;
use MonitoraggioBundle\Service\IGestoreIterProgetto;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Bando;

class GestoreIterProgettoServiceTest extends TestBaseService
{
    public function testCreazioneGestore(){
		$procedura = new Bando();
		$richiesta = new Richiesta();
		$richiesta->setProcedura($procedura);
		$procedura->addRichieste($richiesta);
		
		$gestore = new GestoreIterProgettoService($this->container);
		$res = $gestore->getIstanza($richiesta);

		$this->assertNotNull($res);
		$this->assertInstanceOf(IGestoreIterProgetto::class, $res);
	}
}