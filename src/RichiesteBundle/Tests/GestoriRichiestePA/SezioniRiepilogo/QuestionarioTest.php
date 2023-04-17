<?php
namespace RichiesteBundle\Tests\GestoriRichiestePA\SezioniRiepilogo;

use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Questionario;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\FascicoloProcedura;
use FascicoloBundle\Entity\Fascicolo;
use FascicoloBundle\Entity\Pagina;

class QuestionarioTest extends TestBaseService{

    /**
     * @var IRiepilogoRichiesta
     */
    protected $riepilogo;

    /**
     * @var Questionario
     */
    protected $questionario;

    /**
     * @var Bando
     */
    protected $procedura;

    /**
     * @var Richiesta
     */
    protected $richiesta;

    public function setUp(){
        parent::setUp();
        $this->riepilogo = $this->createMock(IRiepilogoRichiesta::class);
        $this->procedura = new Bando();
        $this->richiesta = new Richiesta();
        $this->richiesta->setProcedura($this->procedura);
        $this->procedura->addRichieste($this->richiesta);
        $this->riepilogo->method('getRichiesta')->willReturn($this->richiesta);
        $this->questionario = new Questionario($this->container, $this->riepilogo);
    }

    public function testFascicoloNonDefinito(){
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Non sono stati definiti fascicoli per la procedura');
        $this->questionario->getUrl();
    }

    public function testIstanziaFascicolo(){
        $fascicoloProcedura = new FascicoloProcedura();
        $fascicolo = new Fascicolo();
        $fascicoloProcedura->setFascicolo($fascicolo);
        $indice = new Pagina();
        $fascicolo->setIndice($indice);
        $this->procedura->addFascicoliProcedura($fascicoloProcedura);
        
        $this->questionario->getUrl();
    }
}