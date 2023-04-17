<?php

namespace RichiesteBundle\Tests\Service;

use RichiesteBundle\Service\GestoreStatoRichiestaService;
use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Service\IGestoreStatoRichiesta;
use RichiesteBundle\Service\GestoreStatoRichiestaBase;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Service\IGestorePianoCosto;
use RichiesteBundle\Service\GestorePianoCostoService;

class GestoreStatoRichiestaBaseTest extends TestBaseService {
    /**
     * @var Richiesta
     */
    protected $richiesta;
    /**
     * @var IGestoreStatoRichiesta
     */
    protected $base;
    public function setUp()
    {
        parent::setUp();
        $this->richiesta = new Richiesta();
        $mandatario = new Proponente($this->richiesta);
        $mandatario->setMandatario(true);
        $this->richiesta->addProponenti($mandatario);
        $this->base = new GestoreStatoRichiestaBase($this->container, $this->richiesta);
    }

    public function testVociMenu()
    {
        $res = $this->base->getVociMenu();
        $this->assertNotEmpty($res);
    }

    public function testPianoCostiEsitatoTotale():void{
        $gestorePianoCosti = $this->createMock(IGestorePianoCosto::class);
        $gestorePianoCosti->expects($this->atLeastOnce())
        ->method('getAnnualita')
        ->willReturn(
            [
                '1' => 'anno1'
            ]
        );
        $gestorePianoCostiService = $this->createMock(GestorePianoCostoService::class);
        $gestorePianoCostiService->expects($this->atLeastOnce())
        ->method('getGestore')->willReturn($gestorePianoCosti);

        $this->container->set('gestore_piano_costo', $gestorePianoCostiService);

        $res = $this->base->visualizzaPianoCosti($this->richiesta->getMandatario());
    }
}
