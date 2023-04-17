<?php

namespace RichiesteBundle\Tests\GestoriRichiestePA\Riepilogo;

use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\DatiGenerali;
use RichiesteBundle\GestoriRichiestePA\Riepilogo\Riepilogo_67;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\DatiProgetto;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Questionario;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\FasiProcedurali;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\PianoCosto;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Indicatori;
use RichiesteBundle\Service\IGestoreRichiesta;
use RichiesteBundle\Service\GestoreRichiestaService;
use SfingeBundle\Entity\ProceduraPA;
use RichiesteBundle\Entity\Proponente as ProponenteRichiesta;
use Symfony\Component\Security\Csrf\CsrfToken;
use RichiesteBundle\GestoriRichiestePA\Azioni\PassaInIstruttoria;


class Riepilogo_67Test  extends \BaseBundle\Tests\Service\TestBaseService{

    /**
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var Riepilogo_67
     */
    protected $riepilogo;

    protected $gestoreRichieste;

    public function setUp(){
        parent::setUp();
        $this->gestoreRichieste = $this->createMock(IGestoreRichiesta::class);
        $gestoreService = $this->createMock(GestoreRichiestaService::class);
        $gestoreService->method('getGestore')->willReturn($this->gestoreRichieste);
        $this->container->set('gestore_richieste', $gestoreService);

        $this->richiesta = new Richiesta();
        $proponente = new ProponenteRichiesta();
        $proponente->setRichiesta($this->richiesta);
        $proponente->setMandatario(true);
        $this->richiesta->addProponente($proponente);
        $this->richiesta->setProcedura(new ProceduraPA());

        $token = new CsrfToken(PassaInIstruttoria::TOKEN_ID, PassaInIstruttoria::TOKEN_CSRF_NAME);
        $tokenManager = $this->container->get('security.csrf.token_manager');
        $tokenManager->method('getToken')->willReturn($token);
        
        $this->riepilogo = new Riepilogo_67($this->container, $this->richiesta);
    }

    public function testCheckSezioni(){
        $sezioni = $this->riepilogo->getSezioni();
        $this->assertNotEmpty($this->filterClass($sezioni, DatiGenerali::class));
        $this->assertNotEmpty($this->filterClass($sezioni, Proponente::class));
        $this->assertNotEmpty($this->filterClass($sezioni, DatiProgetto::class));
        $this->assertNotEmpty($this->filterClass($sezioni, FasiProcedurali::class));
        $this->assertNotEmpty($this->filterClass($sezioni, PianoCosto::class));
        $this->assertNotEmpty($this->filterClass($sezioni, Indicatori::class));
    }

    protected function filterClass(array $array, $classe) : array{
        return \array_filter($array, function($el) use($classe){
            return \is_a($el, $classe);
        });
    }
}