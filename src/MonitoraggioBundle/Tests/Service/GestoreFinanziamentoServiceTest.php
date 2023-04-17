<?php

namespace MonitoraggioBundle\Tests\Service;

use BaseBundle\Tests\Service\TestBaseService;
use MonitoraggioBundle\Service\GestoreFinanziamentoService;
use RichiesteBundle\Entity\Richiesta;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use MonitoraggioBundle\Service\GestoriFinanziamento\Privato;
use MonitoraggioBundle\Service\GestoriFinanziamento\Pubblico;
use RichiesteBundle\Entity\Proponente;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Entity\Azienda;
use SoggettoBundle\Entity\ComuneUnione;
use MonitoraggioBundle\Service\GestoriFinanziamento\Comune;
use MonitoraggioBundle\Service\GestoriFinanziamento\Regione;
use SfingeBundle\Entity\Bando;

class GestoreFinanziamentoServiceTest extends TestBaseService {
    /**
     * @var GestoreFinanziamentoService
     */
    protected $service;

    /**
     * @var Richiesta
     */
	protected $richiesta;
	
	/**
	 * @var Proponente
	 */
	protected $proponente;

    public function setUp() {
        parent::setUp();

        $this->service = new GestoreFinanziamentoService($this->container);
        $this->richiesta = new Richiesta();
        $istruttoria = new IstruttoriaRichiesta();
        $istruttoria->setRichiesta($this->richiesta);
		$this->richiesta->setIstruttoria($istruttoria);
		$this->proponente = new Proponente($this->richiesta);
		$this->proponente->setMandatario(true);
		$this->richiesta->addProponente($this->proponente);
		
		$this->setSoggetto(new Azienda());

		$procedura = new Bando();
		$this->richiesta->setProcedura($procedura);
	}
	
	protected function setSoggetto(Soggetto $soggetto): void{
		$this->proponente->setSoggetto($soggetto);
	}

    protected function setTipologiaSoggetto(bool $pubblico): void {
        $this->richiesta->getIstruttoria()->setTipologiaSoggetto($pubblico ? 'PUBBLICO' : 'PRIVATO');
    }

    public function testSoggettoPrivato() {
		$this->setTipologiaSoggetto(false);
		$this->setSoggetto(new Azienda());
		
        $gestore = $this->service->getGestore($this->richiesta);

        $this->assertInstanceOf(Privato::class, $gestore);
	}
	
	public function testSoggettoPubblico(){
		$this->setTipologiaSoggetto(true);
		$this->setSoggetto(new Soggetto());
		
        $gestore = $this->service->getGestore($this->richiesta);

        $this->assertInstanceOf(Pubblico::class, $gestore);
	}

	public function testComune(){
		$this->setTipologiaSoggetto(true);
		$this->setSoggetto(new ComuneUnione());
		
        $gestore = $this->service->getGestore($this->richiesta);

        $this->assertInstanceOf(Comune::class, $gestore);
	}

	public function testRegione(){
		$this->setTipologiaSoggetto(true);
		$regione = new Soggetto();
		$regione->setId(3438);
		$this->setSoggetto($regione);
		
        $gestore = $this->service->getGestore($this->richiesta);

        $this->assertInstanceOf(Regione::class, $gestore);
	}
}
