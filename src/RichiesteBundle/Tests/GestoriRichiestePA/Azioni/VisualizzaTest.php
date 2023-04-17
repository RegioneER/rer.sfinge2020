<?php
namespace RichiesteBundle\Tests\GestoriRichiestePA\Azioni;

use RichiesteBundle\GestoriRichiestePA\Azioni\Visualizza;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use BaseBundle\Entity\Stato;
use BaseBundle\Entity\StatoRichiesta;

class VisualizzaTest extends \BaseBundle\Tests\Service\TestBaseService{

    /**
     * @var Visualizza
     */
    protected $visualizza;

    /**
     * @var IRiepilogoRichiesta
     */
    protected $riepilogo;
    /**
     * @var Richiesta
     */
    protected $richiesta;

    public function setUp()
    {
        parent::setUp();
        $this->richiesta = new Richiesta();
        $this->riepilogo = $this->createMock(IRiepilogoRichiesta::class);
        $this->riepilogo
            ->method('getRichiesta')
            ->willReturn($this->richiesta);
        $this->visualizza = new Visualizza($this->router, $this->riepilogo);
    }

    public function testIsVisibile(){
        $this->assertTrue($this->visualizza->isVisibile());
    }

    public function testGetRisultatoEsecuzioneSenzaStato(){
        $this->richiesta->setId(1);
        
        $this->router
        ->expects($this->at(1))
        ->method('generate')
        ->with('procedura_pa_nuova_richiesta',
        ['id_richiesta' => 1],
        UrlGeneratorInterface::ABSOLUTE_PATH)
        ->willReturn('someUrl');

        $this->visualizza = new Visualizza($this->router, $this->riepilogo);
        $this->visualizza->getRisultatoEsecuzione();
    }

    public function testGetRisultatoConStato(){
        $statoRichiesta = new StatoRichiesta();
        $this->richiesta->setId(1);
        $this->richiesta->setStato($statoRichiesta);
        
        $this->riepilogo->expects($this->once())->method('getUrl')->willReturn('someUrl');
        
        $this->visualizza->getRisultatoEsecuzione();
    }

    public function testTitolo(){
        $titolo = 'titolo';
        $this->visualizza->setTitolo($titolo);
        $this->assertSame($titolo, $this->visualizza->getTitolo());
    }

    public function testAttr(){
        $val = 'v';
        $attr  = $this->visualizza->addAttr('attr',$val)->getAttr();
        $this->assertContains($val, $this->visualizza->getAttr());
        $this->visualizza->removeAttr('attr');
        $this->assertEmpty($this->visualizza->getAttr());
    }

}