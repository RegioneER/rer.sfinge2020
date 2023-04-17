<?php

namespace RichiesteBundle\Tests\GestoriPianiCosto;

use BaseBundle\Tests\Service\TestBaseService;
use Symfony\Component\HttpFoundation\ParameterBag;
use RichiesteBundle\Service\GestorePianoCostoBase;
use RichiesteBundle\Entity\PianoCostoRepository;
use Symfony\Component\HttpFoundation\Request;
use RichiesteBundle\Entity\RichiestaRepository;
use SfingeBundle\Entity\Bando;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\SezionePianoCosto;

class GestorePianoCostoBaseTest extends TestBaseService {
    /**
     * @var PianoCostoRepository
     */
    protected $pianoCostiRepo;

    /**
     * @var RichiestaRepository
     */
    protected $richiesteRepo;

    /**
     * @var ParameterBag
     */
    protected $parameterBag;

    /**
     * @var GestorePianoCostoBase
     */
    protected $gestore;

    public function setUp() {
        parent::setUp();
        $this->pianoCostiRepo = $this->createMock(PianoCostoRepository::class);
        $this->richiesteRepo = $this->createMock(RichiestaRepository::class);
        $this->em->method('getRepository')->will(
            $this->returnValueMap(array(
                array('RichiesteBundle:PianoCosto', $this->pianoCostiRepo),
                array('RichiesteBundle:Richiesta', $this->richiesteRepo)
        )));
        $this->parameterBag = new ParameterBag();
        $request = new Request();
        $request->query = $this->parameterBag;
        $this->requestStack->push($request);
        $this->gestore = new GestorePianoCostoBase($this->container);
    }

    public function testGetSezioniSenzaBandoInRequestStack() {
        $this->parameterBag->set('id_bando', 1);
        $this->expectExceptionMessage("Nessuna richiesta trovata");

        $this->gestore->getSezioni(null);
    }

    public function testGetSezioniSenzaIdRichiestaInRequestStack() {
        $this->expectExceptionMessage("Nessun id_richiesta indicato");

        $this->gestore->getSezioni(null);
    }

    public function testGetSezioniNoRichiesta(){
        $this->parameterBag->set('id_richiesta', 1);

        $this->expectExceptionMessage("Nessuna richiesta trovata");
        
        $this->gestore->getSezioni(null);
    }

    public function testGetSezioni(){
        $sezione = new SezionePianoCosto();
        $procedura = new Bando();
        $procedura->setId(99);
        $richiesta = new Richiesta();
        $richiesta->setProcedura($procedura);

        $this->parameterBag->set('id_richiesta', 1);

        $this->richiesteRepo->method('find')->willReturn($richiesta);
        $this->pianoCostiRepo->expects($this->once())
        ->method('getSezioniDaProcedura')
        ->with(
            $this->equalTo(99)
        )
        ->willReturn([$sezione]);
        
        $sezioni = $this->gestore->getSezioni(null);

        $this->assertNotNull($sezioni);
        $this->assertContains($sezione, $sezioni);
    }
}
