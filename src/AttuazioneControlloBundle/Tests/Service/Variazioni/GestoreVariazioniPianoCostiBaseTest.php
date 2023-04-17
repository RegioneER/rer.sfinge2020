<?php

namespace AttuazioneControlloBundle\Tests\Service\Variazioni;

use BaseBundle\Tests\Service\TestBaseService;
use AttuazioneControlloBundle\Entity\VariazionePianoCosti;
use AttuazioneControlloBundle\Service\Variazioni\GestoreVariazioniPianoCostiBase;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AttuazioneControlloBundle\Entity\StatoVariazione;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Service\IGestorePianoCosto;
use RichiesteBundle\Service\GestorePianoCostoService;
use SfingeBundle\Entity\Bando;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Utility\EsitoValidazione;
use AttuazioneControlloBundle\Entity\VariazioneVocePianoCosto;
use AttuazioneControlloBundle\Service\Variazioni\IGestoreVariazioni;

class GestoreVariazioniPianoCostiBaseTest extends TestBaseService {
    /**
     * @var VariazionePianoCosti
     */
    protected $variazione;

    /**
     * @var GestoreVariazioniPianoCostiBase
     */
    protected $service;

    /**
     * @var IGestoreVariazioni
     */
    protected $base;

    public function setUp() {
        parent::setUp();
        $atc = new AttuazioneControlloRichiesta();
        $this->variazione = new VariazionePianoCosti($atc);
        $stato = new StatoVariazione();
        $stato->setCodice(StatoVariazione::VAR_INSERITA);
        $this->variazione->setStato($stato);
        $richiesta = new Richiesta();
        $atc->setRichiesta($richiesta);
        $procedura = new Bando();
        $richiesta->setProcedura($procedura);
        $proponente = new Proponente($richiesta);
        $proponente->setMandatario(true);
        $richiesta->addProponenti($proponente);

        $this->base = $this->createMock(IGestoreVariazioni::class);
        $this->service = new GestoreVariazioniPianoCostiBase($this->variazione, $this->base, $this->container);
    }

    public function testDettaglioVariazione(): void {
        $pianoCostoService = $this->createMock(GestorePianoCostoService::class);
        $pianoCostoBase = $this->createMock(IGestorePianoCosto::class);
        $pianoCostoBase->expects($this->atLeastOnce())->method('getAnnualita')->willReturn([]);
        $pianoCostoService->expects($this->atLeastOnce())->method('getGestore')->willreturn($pianoCostoBase);
        $this->container->set('gestore_piano_costo', $pianoCostoService);

        $this->templating->expects($this->once())->method('renderResponse')->with(
            $this->equalTo('AttuazioneControlloBundle:Variazioni:dettaglioVariazionePianoCosti.html.twig'),
            $this->logicalAnd(
                $this->logicalNot($this->isEmpty())
            )
        );
        $this->service->dettaglioVariazione();
    }

    public function xtestPianoCostiVariazione(): void {
        $mandatario = $this->variazione->getRichiesta()->getMandatario();
        $this->service->pianoCostiVariazione($mandatario);
    }

    public function testValidaPianoDeiCostiSenzaVoci() {
        $factory = $this->createMock(GestorePianoCostoService::class);
        $service = $this->createMock(IGestorePianoCosto::class);
        $factory->method('getGestore')->willReturn($service);
        $this->container->set("gestore_piano_costo", $factory);
        $service->expects($this->once())->method('getAnnualita')->willReturn(['1' => 'anno1']);

        $res = $this->service->validaPianoDeiCosti();

        $this->assertInstanceOf(EsitoValidazione::class, $res);
        $this->assertFalse($res->getEsito());
        $this->assertContains("Variazione piano costi non definita", $res->getMessaggiSezione());
    }

    public function testValidaPianoDeiCostiVoceNulla() {
        $factory = $this->createMock(GestorePianoCostoService::class);
        $service = $this->createMock(IGestorePianoCosto::class);
        $factory->method('getGestore')->willReturn($service);
        $this->container->set("gestore_piano_costo", $factory);
        $service->expects($this->once())->method('getAnnualita')->willReturn(['1' => 'anno1']);

        $voceVariazione = new VariazioneVocePianoCosto();
        $this->variazione->addVociPianoCosto($voceVariazione);

        $res = $this->service->validaPianoDeiCosti();

        $this->assertInstanceOf(EsitoValidazione::class, $res);
        $this->assertFalse($res->getEsito());
        $this->assertContains("Variazione piano costi non definita - Premere SALVA all'interno di ogni sezione", $res->getMessaggiSezione());
    }

    public function testValidaPianoDeiCostiPerAnnonIncompletoOk() {
        $factory = $this->createMock(GestorePianoCostoService::class);
        $service = $this->createMock(IGestorePianoCosto::class);
        $factory->method('getGestore')->willReturn($service);
        $this->container->set("gestore_piano_costo", $factory);
        $service->expects($this->once())->method('getAnnualita')->willReturn(['1' => 'anno1', '2' => 'anno2']);

        $voceVariazione = new VariazioneVocePianoCosto();
        $voceVariazione->setImportoVariazioneAnno1(1.00);
        $this->variazione->addVociPianoCosto($voceVariazione);

        $res = $this->service->validaPianoDeiCosti(1);

        $this->assertInstanceOf(EsitoValidazione::class, $res);
        $this->assertTrue($res->getEsito());
    }

    public function testValidaPianoDeiCostiPerAnnonIncompleto() {
        $factory = $this->createMock(GestorePianoCostoService::class);
        $service = $this->createMock(IGestorePianoCosto::class);
        $factory->method('getGestore')->willReturn($service);
        $this->container->set("gestore_piano_costo", $factory);
        $service->expects($this->once())->method('getAnnualita')->willReturn(['1' => 'anno1', '2' => 'anno2']);

        $voceVariazione = new VariazioneVocePianoCosto();
        $this->variazione->addVociPianoCosto($voceVariazione);

        $res = $this->service->validaPianoDeiCosti(1);

        $this->assertInstanceOf(EsitoValidazione::class, $res);
        $this->assertFalse($res->getEsito());
        $this->assertContains("Variazione piano costi non definita - Premere SALVA all'interno di ogni sezione", $res->getMessaggiSezione());
    }

    public function testValidaPianoDeiCostiOk() {
        $factory = $this->createMock(GestorePianoCostoService::class);
        $service = $this->createMock(IGestorePianoCosto::class);
        $factory->method('getGestore')->willReturn($service);
        $this->container->set("gestore_piano_costo", $factory);
        $service->expects($this->once())->method('getAnnualita')->willReturn(['1' => 'anno1']);

        $voceVariazione = new VariazioneVocePianoCosto();
        $voceVariazione->setImportoVariazioneAnno1(1.00);
        $this->variazione->addVociPianoCosto($voceVariazione);

        $res = $this->service->validaPianoDeiCosti();

        $this->assertInstanceOf(EsitoValidazione::class, $res);
        $this->assertTrue($res->getEsito());
        $this->assertEmpty($res->getMessaggiSezione());
    }
}
