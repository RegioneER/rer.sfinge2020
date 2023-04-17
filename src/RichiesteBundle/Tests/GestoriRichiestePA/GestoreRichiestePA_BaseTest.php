<?php

namespace RichiesteBundle\Tests\GestoriRichiestePA;

use RichiesteBundle\GestoriRichiestePA\GestoreRichiestePA_Base;
use SfingeBundle\Entity\ProceduraPA;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\GestoriRichiestePA\Riepilogo\Riepilogo_Base;
use RichiesteBundle\Entity\Proponente;
use SoggettoBundle\Entity\Azienda;
use Symfony\Component\Form\Form;
use SoggettoBundle\Entity\Sede;
use RichiesteBundle\Entity\OggettoRichiesta;
use GeoBundle\Entity\GeoComune;
use BaseBundle\Tests\Service\TestBaseService;
use MonitoraggioBundle\Service\IGestoreIterProgetto;
use MonitoraggioBundle\Service\GestoreIterProgettoService;
use Symfony\Component\Security\Csrf\CsrfToken;
use RichiesteBundle\GestoriRichiestePA\Azioni\PassaInIstruttoria;

class GestoreRichiestePA_BaseTest extends TestBaseService {

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|IGestoreIterProgetto
     */
    protected $iterBase;
    public function setUp() {
        parent::setUp();
        $this->iterBase = $this->createMock(IGestoreIterProgetto::class);
        $iterService = $this->createMock(GestoreIterProgettoService::class);
        $iterService->method('getIstanza')->willReturn($this->iterBase);

        $this->container->set('monitoraggio.iter_progetto', $iterService);
    }

    public function testGetRiepilogoRichiestaInstance() {
        $this->logger->method('debug')
            ->willReturn(null);
        $this->router->method('generate')
        ->willReturn(null);

        $token = new CsrfToken(PassaInIstruttoria::TOKEN_ID, PassaInIstruttoria::TOKEN_CSRF_NAME);
        $tokenManager = $this->container->get('security.csrf.token_manager');
        $tokenManager->method('getToken')->willReturn($token);
        
        $richiesta = $this->createRichiesta('procedura inesistente');
        $gestore = new GestoreRichiestePA_Base($this->container, $richiesta);
        $riepilogo = $gestore->getRiepilogoRichiestaInstance();

        $this->assertNotNull($riepilogo);
        $this->assertInstanceOf(Riepilogo_Base::class, $riepilogo);
    }

    public function testNuovaRichiesta() {
        $soggettoRepositoryMock = $this->createMock(\SoggettoBundle\Entity\SoggettoRepository::class);
        $this->em->method('getRepository')
            ->with('SoggettoBundle:Soggetto')
            ->willReturn($soggettoRepositoryMock);
        $this->templating->method('renderResponse')
        ->willReturn(null);
        $form = $this->createMock(Form::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);

        $richiesta = $this->createRichiesta(60);
        $soggetto = $richiesta->getMandatario()->getSoggetto();

        $comune = new GeoComune();
        $soggetto->setComune($comune);
        $soggetto->setVia('via dei pazzi');

        $this->em->expects($this->at(2))
            ->method('persist')
            ->with($this->isInstanceOf(OggettoRichiesta::class));

        $this->em->expects($this->at(3))
            ->method('persist')
            ->with($this->logicalAnd(
                $this->isInstanceOf(Richiesta::class)
            ));

        $this->iterBase->expects($this->once())->method('aggiungiFasiProcedurali');
        
        $this->em->expects($this->at(4))
            ->method('persist')
            ->with($this->logicalAnd(
                $this->isInstanceOf(Sede::class),
                $this->attributeEqualTo('soggetto', $soggetto),
                $this->callback(function ($sede) use ($soggetto) { /** @var Sede $sede */
                    $indirizzo = $sede->getIndirizzo();
                    $res = !\is_null($indirizzo);
                    $res = $res && $indirizzo->getVia() == $soggetto->getVia();

                    return $res;
                })
            ));

        $this->formFactory->method('create')
            ->willReturn($form);

        $gestore = new GestoreRichiestePA_Base($this->container, $richiesta);
        $gestore->nuovaRichiesta();
    }

    /**
     * @return Richiesta
     */
    protected function createRichiesta($id_procedura) {
        $procedura = new ProceduraPA();
        $procedura->setId($id_procedura);

        $richiesta = new Richiesta();
        $richiesta->setProcedura($procedura);

        $soggettoMandatario = new Azienda();
        $mandatario = new Proponente();
        $mandatario->setMandatario(true);
        $mandatario->setRichiesta($richiesta);
        $mandatario->setSoggetto($soggettoMandatario);

        $richiesta->addProponenti($mandatario);

        return $richiesta;
    }
}
