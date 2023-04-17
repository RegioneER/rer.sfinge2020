<?php

namespace MonitoraggioBundle\Tests\Service;

use MonitoraggioBundle\Service\GestoreImpegniService;
use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\Entity\Richiesta;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use MonitoraggioBundle\Service\IGestoreImpegni;
use MonitoraggioBundle\Service\GestoriImpegni\Privato;
use MonitoraggioBundle\Service\GestoriImpegni\Pubblico;
use SfingeBundle\Entity\Bando;

class GestoreImpegniServiceTest extends TestBaseService {
    /**
     * @var GestoreImpegniService
     */
	private $service;
	
	/**
	 * @var Richiesta
	 */
	protected $richiesta;

    public function setUp() {
        parent::setUp();
		$this->service = new GestoreImpegniService($this->container);
		$this->richiesta = new Richiesta();
		$istruttoria = new IstruttoriaRichiesta();
		$istruttoria->setRichiesta($this->richiesta);
		$this->richiesta->setIstruttoria($istruttoria);
		$procedura = new Bando();
		$this->richiesta->setProcedura($procedura);
	}
	
	public function testIstanzaPrivato(){
		$this->setTipologiaSoggetto('PRIVATO');

		$gestore = $this->service->getGestore($this->richiesta);

		$this->assertNotNull($gestore);
		$this->assertInstanceOf(IgestoreImpegni::class, $gestore);
		$this->assertInstanceOf(Privato::class, $gestore);
	}

	protected function setTipologiaSoggetto(string $tipologia): void{
		$this->richiesta->getIstruttoria()->setTipologiaSoggetto($tipologia);
	}

	public function testIstanzaPubblico(){
		$this->setTipologiaSoggetto('PUBBLICO');

		$gestore = $this->service->getGestore($this->richiesta);

		$this->assertNotNull($gestore);
		$this->assertInstanceOf(IgestoreImpegni::class, $gestore);
		$this->assertInstanceOf(Pubblico::class, $gestore);
	}
}
