<?php

namespace AttuazioneControlloBundle\Tests\CalcoloIndicatori;

use FascicoloBundle\Services\IstanzaFascicolo;
use AttuazioneControlloBundle\CalcoloIndicatori\Indicatore_2_A_12014IT16RFOP008;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Entity\OggettoRichiesta;

class Indicatore_2_A_12014IT16RFOP008Test extends TestIndicatore {

    /** 
     * @var IstanzaFascicolo
     */
    protected $service;

    public function  setUp(){
        parent::setUp();
        $this->service = $this->createMock(IstanzaFascicolo::class);
        $this->container->set('fascicolo.istanza', $this->service);
    }
    
    public function testCalcolaValoreRealizzato() {
        $this->richiesta->getProcedura()->setId(1);

        $oggettoRichiesta = new OggettoRichiesta();
        $istanzaFascicolo = new \FascicoloBundle\Entity\IstanzaFascicolo();
        $oggettoRichiesta->setIstanzaFascicolo($istanzaFascicolo);
        $this->richiesta->addOggettoRichiesta($oggettoRichiesta);

        $collection = array(1,2,3,4);

        $this->service->expects($this->atLeastOnce())
        ->method('get')
        ->with(
            $this->isInstanceOf(\FascicoloBundle\Entity\IstanzaFascicolo::class),
            $this->equalTo('banda_larga_2016.indice.sezione_1.ubicazione_area.elenco_vie.form')
        )
        ->willReturn($collection);

        $calcolo = new Indicatore_2_A_12014IT16RFOP008($this->container, $this->richiesta);
        $res = $calcolo->getValore();
        $this->assertNotNull($res);
        $this->assertSame(4.0, $res);
    }

    public function testProceduraNon1(){
        $this->richiesta->getProcedura()->setId(2);
        $this->expectException( \Exception::class );
        $this->expectExceptionMessage('Calcolo implementato solo per bando "banda larga 2016"');

        $calcolo = new Indicatore_2_A_12014IT16RFOP008($this->container, $this->richiesta);
        $calcolo->getValore();
    }
}
